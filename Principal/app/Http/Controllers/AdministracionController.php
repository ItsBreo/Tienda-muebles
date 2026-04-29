<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Furniture;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

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
            $cookieName = 'preferencias_'.$activeUser->id;
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
        // Obtenemos el sesionId de la petición.
        // Lo buscamos en la ruta, en la query string, en los inputs del formulario
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');

        if (!$sesionId) {
            $sesionId = $request->cookie('current_sesionId');
        }

        if (!$sesionId) {
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        $user = User::activeUserSesion($sesionId);

        // Comprobamos si existe un usuario para esa sesión.
        if (! $user) {
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        // Comprobamos si el usuario es admin usando la función dedicada
        if ($user->isAdmin()) {
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
        $mueble = $muebles->first(fn ($m) => $m->getId() == (int) $id);

        if (! $mueble) {
            abort(404, 'Mueble no encontrado');
        }

        // 3. Crear cookie de "Visto recientemente"
        Cookie::queue("mueble_{$mueble->getId()}", json_encode($mueble), 60 * 24 * 30);

        // 4. Retornamos la vista PÚBLICA
        return view('muebles.show', array_merge($sesionData, [
            'mueble' => $mueble,
        ]));
    }


    public function index(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');
        if (!$sesionId) {
            $sesionId = $request->cookie('current_sesionId');
        }

        $search = $request->input('search');

        $query = Furniture::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }
        $muebles = $query->get();

        return view('admin.muebles.index', compact('muebles', 'sesionId', 'search'));
    }

    public function create(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId');

        $categories = Category::all();

        return view('admin.muebles.create', compact('categories', 'sesionId'));
    }

    public function store(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Validar
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'materials' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'main_color' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Checkbox manual
        $data['is_salient'] = $request->has('is_salient');

        // Crear Mueble (El ID se genera solo en la DB, no calculamos MaxId)
        $mueble = Furniture::create($data);

        // Imagen
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|max:2048']);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            // Crear relación en tabla images
            $mueble->images()->create([
                'image_path' => 'images/'.$imageName,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble creado correctamente.');
    }

    public function show(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId');

        // Laravel ya buscó el mueble por ti. Si no existe, da error 404 solo.
        return view('admin.muebles.show', compact('mueble', 'sesionId'));
    }

    public function edit(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId');

        $categories = Category::all();

        return view('admin.muebles.edit', compact('mueble', 'categories', 'sesionId'));
    }

    public function update(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Validar
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'materials' => 'nullable|string',
            'dimensions' => 'nullable|string',
            'main_color' => 'required|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Checkbox
        $data['is_salient'] = $request->has('is_salient');

        // Actualizamos
        $mueble->update($data);

        // Imagen nueva (opcional)
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|max:2048']);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            // Quitar prioridad a las anteriores
            $mueble->images()->update(['is_primary' => false]);

            // Crear la nueva
            $mueble->images()->create([
                'image_path' => 'images/'.$imageName,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble actualizado correctamente.');
    }

    public function destroy(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // BORRADO DB
        $mueble->delete();

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble eliminado correctamente.');
    }

    public function indexCategorias(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');
        if (!$sesionId) {
            $sesionId = $request->cookie('current_sesionId');
        }

        $search = $request->input('search');

        $query = Category::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $categorias = $query->get();

        return view('admin.categorias.index', compact('categorias', 'sesionId', 'search'));
    }
}
