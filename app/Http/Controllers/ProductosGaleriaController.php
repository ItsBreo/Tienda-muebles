<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File; // Importante para borrar archivos

class ProductosGaleriaController extends Controller
{
    private $cookieName = 'muebles_crud';
    private $cookieMinutes = 60 * 24 * 7; // 1 semana

    // --- FUNCIONES COPIADAS DE AdministracionController ---

    /**
     * Obtiene los muebles desde la cookie o los datos mock.
     */
    private function getMuebles()
    {
        $mueblesJson = Cookie::get($this->cookieName);
        if ($mueblesJson) {
            return collect(json_decode($mueblesJson, true))->map(function ($item) {
                return new Furniture(
                    $item['id'], $item['category_id'], $item['name'], $item['description'],
                    $item['price'], $item['stock'], $item['materials'] ?? '', $item['dimensions'] ?? '',
                    $item['main_color'], $item['is_salient'], $item['images']
                );
            });
        }
        $muebles = collect(Furniture::getMockData());
        $this->saveMuebles($muebles);
        return $muebles;
    }

    /**
     * Guarda la colección de muebles en la cookie.
     */
    private function saveMuebles($muebles)
    {
        Cookie::queue($this->cookieName, $muebles->toJson(), $this->cookieMinutes);
    }

    // --- MÉTODOS DEL CONTROLADOR (MODIFICADOS) ---

    /**
     * Sube y guarda las imágenes (en la cookie).
     */
    public function store(Request $request, $id)
    {
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
                $image->move(public_path('images'), $imageName); // Mueve el archivo físico
                $imagePaths[] = $imageName; // Añade el nombre al array
            }
        }

        $mueble->setImages($imagePaths); // Actualiza el objeto mueble
        $muebles[$muebleIndex] = $mueble; // Reemplaza en la colección
        $this->saveMuebles($muebles); // Guarda la colección en la cookie

        return back()->with('success', 'Imágenes subidas correctamente.');
    }

    /**
     * Elimina una imagen de un mueble (de la cookie).
     * Nota: El parámetro $id viene de {mueble} y $imageName de {image} en la ruta.
     */
    public function destroy($id, $imageName)
    {
        $muebles = $this->getMuebles();
        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) {
            return back()->with('error', 'Mueble no encontrado.');
        }

        $mueble = $muebles[$muebleIndex];
        $currentImages = $mueble->getImages();

        // Filtra el array, quitando la imagen a borrar
        $newImages = array_filter($currentImages, fn($img) => $img !== $imageName);

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
