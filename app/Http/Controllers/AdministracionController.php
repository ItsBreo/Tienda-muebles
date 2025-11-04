<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Category;

class AdministracionController extends Controller
{

    private $sessionKey = 'muebles_crud_session';

    private $cookieMinutes = 60 * 24 * 7;


    private function getMuebles()
    {

        $muebles = Session::get($this->sessionKey);


        if ($muebles instanceof \Illuminate\Support\Collection) {
            return $muebles;
        }

        // Si no, carga los datos mock (que ya son Objetos Furniture)
        $muebles = collect(Furniture::getMockData());

        // Los guarda en la SESIÓN para la próxima vez
        $this->saveMuebles($muebles);
        return $muebles;
    }

    /**
     * Guarda la colección de muebles en la Sesión.
     */
    private function saveMuebles($muebles)
    {
        Session::put($this->sessionKey, $muebles);
    }


    /**
     * Revisa si el usuario actual es un admin.
     * Devuelve 'true' si es admin, o una Redirección si no lo es.
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
                         ->with('error-admin', 'Acceso denigado. No tienes permisos de administrador.');
    }


    /**
     * Muestra el listado de muebles.
     */

    public function index(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige


        $muebles = $this->getMuebles();

        // Pasamos los muebles a la vista del panel de administración

        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.index', compact('muebles', 'sesionId'));
    }

    /**
     * Muestra el formulario para crear un nuevo mueble.
     */

    public function create(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige


        $categories = Category::getMockData();
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.create', compact('sesionId', 'categories'));
    }

    /**
     * Guarda un nuevo mueble en la cookie.
     */
    public function store(Request $request)
    {

        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige


        $muebles = $this->getMuebles();


        $maxId = $muebles->max(fn($m) => $m->getId()) ?? 0;

        $newMuebleData = $request->all();
        $newMuebleData['id'] = $maxId + 1;

        $imagePath = 'default.jpg';
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $imagePath = 'images/'.$imageName;
        }

        /*
if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $imagePath = 'images/'.$imageName;
            $mueble->setImages([$imagePath]);
        }

        */

        // Creamos una instancia de Furniture para mantener la consistencia
        $newMueble = new Furniture(
            $newMuebleData['id'],
            (int)$newMuebleData['category_id'],
            $newMuebleData['name'],
            $newMuebleData['description'],
            (float)$newMuebleData['price'],
            (int)$newMuebleData['stock'],
            $newMuebleData['materials'] ?? '',
            $newMuebleData['dimensions'] ?? '',
            $newMuebleData['main_color'],
            $request->has('is_salient'),
            [$imagePath]
        );


        $muebles->push($newMueble);

        $this->saveMuebles($muebles);


        $sesionId = $request->input('sesionId');
        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble creado correctamente.');
    }

    /**
     * Muestra los detalles de un mueble específico.
     */

    public function show(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige


        $muebles = $this->getMuebles();


        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404);
        }


        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.show', compact('mueble', 'sesionId'));
    }


    /**
     * Muestra el formulario para editar un mueble.
     */

    public function edit(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige


        $muebles = $this->getMuebles();


        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404);
        }


        $categories = Category::getMockData();
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.edit', compact('mueble', 'sesionId', 'categories'));
    }

    public function update(Request $request, $id)
    {

        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige


        $muebles = $this->getMuebles();


        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) {
            abort(404);
        }

        $mueble = $muebles[$muebleIndex];

        // Asignación de todos los campos del formulario
        $mueble->setName($request->input('name', $mueble->getName()));
        $mueble->setDescription($request->input('description', $mueble->getDescription()));
        $mueble->setPrice((float)$request->input('price', $mueble->getPrice()));
        $mueble->setMainColor($request->input('main_color', $mueble->getMainColor()));
        $mueble->setIsSalient($request->has('is_salient'));
        $mueble->setStock((int)$request->input('stock', $mueble->getStock()));
        $mueble->setCategoryId((int)$request->input('category_id', $mueble->getCategoryId()));
        $mueble->setMaterials($request->input('materials', $mueble->getMaterials()));
        $mueble->setDimensions($request->input('dimensions', $mueble->getDimensions()));


        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $imagePath = 'images/'.$imageName;
            $mueble->setImages([$imagePath]);
        }



        $muebles[$muebleIndex] = $mueble;
        $this->saveMuebles($muebles);

        $sesionId = $request->input('sesionId');
        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble actualizado correctamente.');
    }

    /**
     * Elimina un mueble de la cookie.
     */

    public function destroy(Request $request, $id)
    {

        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige


        $muebles = $this->getMuebles();


        $muebles = $muebles->reject(fn($m) => $m->getId() == (int)$id)->values();

        $this->saveMuebles($muebles);

        $sesionId = $request->input('sesionId');
        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble eliminado correctamente.');
    }
}
