<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class CategoriaController extends Controller
{

    /**
     * Middleware manual para proteger rutas de admin.
     */
    private function checkAdmin(Request $request)
    {
        // 1. Obtenemos el sesionId de la petición.
        // Lo buscamos en la ruta, en la query string, en los inputs del formulario o en la sesión de Laravel
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');

        // Si no lo encontramos en la petición, buscamos en la sesión de Laravel
        if (!$sesionId) {
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
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // DB: Traemos todas las categorías
        $categorias = Category::all();

        return view('admin.categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        $categorias = Category::all();

        return view('admin.categorias.create', compact('categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Validar datos
        $data = $request -> validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $categoria = Category::create($data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Category $categoria)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        return view('admin.categorias.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Category $categoria)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        return view('admin.categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $categoria)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Validar datos
        $data = $request -> validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$categoria->id,
            'description' => 'nullable|string',
        ]);

        $categoria->update($data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $categoria)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        $categoria->delete();

        return redirect()->route('admin.categorias.index')->with('success', 'Categoría eliminada correctamente.');
    }
}
