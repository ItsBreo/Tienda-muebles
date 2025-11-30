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
        // 1. Obtenemos el sesionId de la petición.
        // Lo buscamos en la ruta, en la query string, en los inputs del formulario o en la sesión de Laravel
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');

        // Si no lo encontramos en la petición, buscamos en la sesión de Laravel
        if (! $sesionId) {
            // Obtenemos el primer sesionId disponible en el array 'usuarios'
            $usuarios = Session::get('usuarios', []);
            $sesionId = array_key_first($usuarios);
        }

        $user = User::activeUserSesion($sesionId);

        // 2. Comprobamos si existe un usuario para esa sesión.
        if (! $user) {
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

    // ---------------------------------------------------
    // MÉTODOS DE ADMINISTRACIÓN (CRUD)
    // ---------------------------------------------------

    // ---------------------------------------------------
    // CAMBIOS EN GENERAL:
    // Ahora en base de datos solo se pasa el mueble y Laravel ya encuentra el indicado para el CRUD.
    // Ya no hace falta buscarlo en cada función
    // ---------------------------------------------------
    public function index(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // DB: Traemos todos (paginados si fueran muchos, pero all() vale por ahora)
        $muebles = Furniture::all();

        return view('admin.muebles.index', compact('muebles'));
    }

    public function create(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        $categories = Category::all();

        return view('admin.muebles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // 1. Validar
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

        // 2. Checkbox manual
        $data['is_salient'] = $request->has('is_salient');

        // 3. Crear Mueble (El ID se genera solo en la DB, no calculamos MaxId)
        $mueble = Furniture::create($data);

        // 4. Imagen
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

    // ========================================================================
    // AQUÍ VIENE LA MAGIA: Route Model Binding
    // En lugar de ($id), pedimos (Furniture $mueble)
    // ========================================================================

    public function show(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Laravel ya buscó el mueble por ti. Si no existe, da error 404 solo.
        return view('admin.muebles.show', compact('mueble'));
    }

    public function edit(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        $categories = Category::all();

        return view('admin.muebles.edit', compact('mueble', 'categories'));
    }

    public function update(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // 1. Recogemos datos
        $data = $request->all();

        // 2. Checkbox
        $data['is_salient'] = $request->has('is_salient');

        // 3. Actualizamos (¡Una sola línea!)
        $mueble->update($data);

        // 4. Imagen nueva (opcional)
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'image|max:2048']);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            // Opcional: Quitar prioridad a las anteriores
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

        // BORRADO DB: ¡Directo y simple!
        $mueble->delete();

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble eliminado correctamente.');
    }
}
