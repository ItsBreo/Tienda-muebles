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
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');

        if (!$sesionId) {
            $sesionId = $request->cookie('current_sesionId');
        }

        if (!$sesionId) {
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        $user = User::activeUserSesion($sesionId);

        // 2. Comprobamos si existe un usuario para esa sesión.
        if (! $user) {
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        // 3. Comprobamos si el usuario es admin
        if ($user->isAdmin()) {
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

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId');

        // DB: Traemos todas las categorías
        $categorias = Category::all();

        return view('admin.categorias.index', compact('categorias', 'sesionId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId');

        $categorias = Category::all();

        return view('admin.categorias.create', compact('categorias', 'sesionId'));
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

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId');

        return view('admin.categorias.show', compact('categoria', 'sesionId'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Category $categoria)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId');

        return view('admin.categorias.edit', compact('categoria', 'sesionId'));
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
