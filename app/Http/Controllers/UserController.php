<?php

namespace App\Http\Controllers\Admin; // Mantenemos el namespace Admin para organización

use App\Http\Controllers\Controller; // Importante extender del Controller base correcto
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Para encriptar contraseñas

class UserController extends Controller
{
    /**
     * Mostrar un listado de todos los usuarios.
     */
    public function index(Request $request)
    {
        // Obtenemos el sesionId para los enlaces del layout
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');

        // Cargamos todos los usuarios con su rol (Eager Loading para optimizar)
        $listaUsuarios = User::with('role')->get();

        return view('admin.usuarios.index', compact('listaUsuarios', 'sesionId'));
    }

    /**
     * Mostrar el formulario para crear un nuevo usuario.
     */
    public function create(Request $request)
    {
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');

        // Pasamos todos los roles disponibles para que el admin elija
        $roles = Role::all();

        return view('admin.usuarios.create', compact('roles', 'sesionId'));
    }

    /**
     * Almacenar un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        $sesionId = $request->input('sesionId');

        // 1. Validación completa
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4', // Podrías añadir 'confirmed' si usas doble campo
            'role_id' => 'required|exists:roles,id',
        ]);

        // 2. Crear el usuario
        User::create([
            'name' => $request->nombre,
            'surname' => $request->apellidos,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encriptamos la contraseña
            'role_id' => $request->role_id,
            'failed_attempts' => 0, // Inicializamos contador de seguridad
        ]);

        return redirect()->route('admin.usuarios.index', ['sesionId' => $sesionId])
                         ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Mostrar el formulario para editar un usuario.
     */
    public function edit(Request $request, User $usuario)
    {
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');

        // Obtenemos roles para el desplegable
        $roles = Role::all();

        return view('admin.usuarios.edit', compact('usuario', 'roles', 'sesionId'));
    }

    /**
     * Actualizar el usuario especificado en la base de datos.
     */
    public function update(Request $request, User $usuario)
    {
        $sesionId = $request->input('sesionId');

        // 1. Validación (ignorando el email del usuario actual)
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|min:4', // Opcional al editar
        ]);

        // 2. Preparar datos
        $data = [
            'name' => $request->nombre,
            'surname' => $request->apellidos,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        // Solo actualizamos la contraseña si se escribió una nueva
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // 3. Actualizar
        $usuario->update($data);

        return redirect()->route('admin.usuarios.index', ['sesionId' => $sesionId])
                         ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar el usuario especificado de la base de datos.
     */
    public function destroy(Request $request, User $usuario)
    {
        $sesionId = $request->input('sesionId');

        // Evitar que un admin se borre a sí mismo (opcional pero recomendado)
        if ($usuario->id == auth()->id()) { // O usando tu lógica de sesión manual
             return redirect()->route('admin.usuarios.index', ['sesionId' => $sesionId])
                              ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index', ['sesionId' => $sesionId])
                         ->with('success', 'Usuario eliminado correctamente.');
    }
}
