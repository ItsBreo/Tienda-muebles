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
    
    /**
     * Obtiene los muebles de la sesión o carga los Mock Data.
     * Esta lógica ahora sirve tanto para Admin como para Público.
     */
    private function getMuebles()
    {
        $muebles = Session::get($this->sessionKey);

        if ($muebles instanceof \Illuminate\Support\Collection) {
            return $muebles;
        }

        // Si no existe sesión, carga Mock Data
        $muebles = collect(Furniture::getMockData());
        $this->saveMuebles($muebles);

        return $muebles;
    }

    private function saveMuebles($muebles)
    {
        Session::put($this->sessionKey, $muebles);
    }

    /**
     * Lógica traída de MuebleController para manejar preferencias de usuario/cookies.
     */
    private function getSesionYPreferencias(Request $request)
    {
        $activeSesionId = $request->query('sesionId');
        $activeUser = null;

        $defaultPrefs = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        if ($activeSesionId) {
            $activeUser = User::activeUserSesion($activeSesionId);
        }

        if ($activeUser) {
            $cookieName = 'preferencias_' . $activeUser->id;
            $cookieData = json_decode($request->cookie($cookieName), true);
            $preferencias = $cookieData ? array_merge($defaultPrefs, $cookieData) : $defaultPrefs;
        } else {
            $preferencias = $defaultPrefs;
        }

        return compact('activeSesionId', 'activeUser', 'preferencias');
    }

    /**
     * Middleware manual para proteger rutas de admin.
     */
    private function checkAdmin(Request $request)
    {
        // 1. Obtenemos el sesionId de la petición.
        // Lo buscamos en la ruta, en la query string y en los inputs del formulario para no perderlo.
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');


        $user = User::activeUserSesion($sesionId);

        // 2. Comprobamos si existe un usuario para esa sesión.
        if (!$user) {
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        // 3. Comprobamos si el usuario tiene el rol 'Admin'.
        if ($user->hasRole('Admin')) {
            return true;
        }

        // No es admin. Le redirigimos a la página principal con un error.
        return redirect()->route('principal', ['sesionId' => $sesionId])
                         ->with('error-admin', 'Acceso denegado. No tienes permisos de administrador.');
    }

    /**
     * Muestra el detalle del mueble al CLIENTE (Público).
     * Antes: MuebleController@show
     * Ahora: AdministracionController@showPublic
     */
    public function showPublic(Request $request, $id)
    {
        // 1. Cargamos preferencias (Lógica traída de MuebleController)
        $sesionData = $this->getSesionYPreferencias($request);

        // 2. Buscamos el mueble usando el helper compartido
        $muebles = $this->getMuebles();
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404, 'Mueble no encontrado');
        }

        // 3. Crear cookie de "Visto recientemente"
        Cookie::queue("mueble_{$mueble->getId()}", json_encode($mueble), 60 * 24 * 30);

        // 4. Retornamos la vista PÚBLICA
        return view('muebles.show', array_merge($sesionData, [
            'mueble' => $mueble,
        ]));
    }

    // ---------------------------------------------------
    // MÉTODOS DE ADMINISTRACIÓN (CRUD)
    // ---------------------------------------------------

    public function index(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) return $check;

        $sesionId = $request->query('sesionId');
        $muebles = Furniture::all(); // <-- ¡AQUÍ ESTÁ EL CAMBIO!

        // Pasamos los muebles a la vista del panel de administración
        return view('admin.muebles.index', compact('muebles', 'sesionId'));
    }

    public function create(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $sesionId = $request->query('sesionId');
        $categories = Category::getMockData();
        return view('admin.muebles.create', compact('categories', 'sesionId'));
    }

    public function store(Request $request)
    {

        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $sesionId = $request->input('sesionId');
        $muebles = $this->getMuebles();
        $maxId = $muebles->max(fn($m) => $m->getId()) ?? 0;

        $newMuebleData = $request->all();
        $imagePath = 'default.jpg';

        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|max:2048']);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $imagePath = 'images/'.$imageName;
        }

        $newMueble = new Furniture(
            $maxId + 1,
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


        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble creado correctamente.');
    }

    /**
     * Muestra el detalle del mueble al ADMIN.
     */
    public function show(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $sesionId = $request->query('sesionId');
        $muebles = $this->getMuebles();
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) abort(404);

        return view('admin.muebles.show', compact('mueble', 'sesionId'));
    }

    public function edit(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $sesionId = $request->query('sesionId');
        $muebles = $this->getMuebles();
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404);
        }

        $categories = Category::getMockData();
        return view('admin.muebles.edit', compact('mueble', 'categories', 'sesionId'));
    }

    public function update(Request $request, $id)
    {

        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $sesionId = $request->input('sesionId');
        $muebles = $this->getMuebles();
        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) abort(404);

        $mueble = $muebles[$muebleIndex];

        // Actualizamos campos (simplificado para lectura)
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
            $request->validate(['image' => 'image|max:2048']);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $mueble->setImages(['images/'.$imageName]);
        }

        $muebles[$muebleIndex] = $mueble;
        $this->saveMuebles($muebles);

        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble actualizado correctamente.');
    }

    public function destroy(Request $request, $id)
    {

        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $sesionId = $request->input('sesionId');
        $muebles = $this->getMuebles();
        $muebles = $muebles->reject(fn($m) => $m->getId() == (int)$id)->values();
        $this->saveMuebles($muebles);

        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble eliminado correctamente.');
    }
}
