<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File; // Importante para borrar archivos

class ProductosGaleriaController extends Controller
{
    private $sessionKey = 'muebles_crud_session';

    private function getMuebles()
    {
        // Intenta obtener los muebles de la sesión
        $muebles = Session::get($this->sessionKey);

        // Si la sesión tiene una colección de muebles, la devuelve
        if ($muebles instanceof \Illuminate\Support\Collection) {
            return $muebles;
        }

        // Si no, carga los datos mock
        $muebles = collect(Furniture::getMockData());

        // Guarda los muebles en la sesión para futuros usos
        Session::put($this->sessionKey, $muebles);

        return $muebles;
    }

    /**
     * Guarda la colección de muebles en la Sesión.
     */
    private function saveMuebles($muebles)
    {
        Session::put($this->sessionKey, $muebles);
    }

    // --- MÉTODOS DEL CONTROLADOR (MODIFICADOS) ---

    /**
     * Sube y guarda las imágenes (en la cookie).
     */
    private function checkAdmin(Request $request)
    {

        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');

        if (!$sesionId) {
            // Si no hay sesionId, no está autenticado.
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión.');
        }

        // Buscamos al usuario en el array de la sesión
        $usuarios = Session::get('usuarios', []);
        $userJson = $usuarios[$sesionId] ?? null;

        if (!$userJson) {
            // Si el sesionId no está en la sesión, se ha invalidado.
            return redirect()->route('login.show')->with('error', 'Tu sesión ha expirado.');
        }

        // Si el usuario existe, comprobamos el rol
        $userData = json_decode($userJson);


        if ($userData && $userData->rol === 'admin') {
            return true;
        }

        // No es admin. Le redirigimos a la página principal con un error.
        return redirect()->route('principal', ['sesionId' => $sesionId])
                         ->with('error-admin', 'Acceso denegado. No tienes permisos de administrador.');
    }

    public function store(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $muebles = $this->getMuebles();
        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) {
            return back()->with('error', 'Mueble no encontrado.');
        }

        $mueble = $muebles[$muebleIndex];
        $imagePaths = $mueble->getImages();

        // Si la única imagen es 'default.jpg', la limpiamos para añadir las nuevas
        if (count($imagePaths) == 1 && $imagePaths[0] == 'default.jpg') {
            $imagePaths = [];
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->extension();
                $image->move(public_path('/images'), $imageName); // Mueve el archivo físico
                $imagePaths[] = 'images/'.$imageName; // Añade el nombre al array
            }
        }

        $mueble->setImages($imagePaths); // Actualiza el objeto mueble
        $muebles[$muebleIndex] = $mueble; // Reemplaza en la colección
        $this->saveMuebles($muebles); // Guarda la colección en la sesión

        return back()->with('success', 'Imágenes subidas correctamente.');
    }

    /**
     * Elimina una imagen de un mueble (de la sesión).
     * Nota: El parámetro $id viene de {mueble} y $imageName de {image} en la ruta.
     */
    public function destroy(Request $request, $id, $imageName)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $muebles = $this->getMuebles();
        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) {
            return back()->with('error', 'Mueble no encontrado.');
        }

        $mueble = $muebles[$muebleIndex];
        $currentImages = $mueble->getImages();

        // Filtra el array, quitando la imagen a borrar
        $newImages = array_filter($currentImages, fn($img) => basename($img) !== $imageName);

        // Borra el archivo físico del servidor
        if (File::exists(public_path('images/' . $imageName))) {
            File::delete(public_path('images/' . $imageName));
        }

        // Si nos quedamos sin imágenes, volvemos a poner 'default.jpg'
        if (empty($newImages)) {
            $mueble->setImages(['default.jpg']);
        } else {
            // Re-indexamos el array
            $mueble->setImages(array_values($newImages));
        }

        $muebles[$muebleIndex] = $mueble;
        $this->saveMuebles($muebles);

        return back()->with('success', 'Imagen eliminada correctamente.');
    }
}
