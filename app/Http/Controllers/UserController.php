<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Mostrar un listado de todos los usuarios
    public function index()
    {
        $listaUsuarios = User::all();
        //return $listaUsuarios;
        //dd($listaUsuarios);
        return view('usuarios.index', compact('listaUsuarios'));
    }

    // Crear un usuario
    public function create()
    {
        // Ya no necesitamos pasar los roles a la vista de creación.
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        // 1. Validación de formularios simplificada.
        $request->validate([
            'nombre' => 'required',
            'apellidos' => 'required',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:4',
        ]);

        // 2. Buscamos el rol 'Cliente' en la base de datos.
        $clienteRole = Role::where('name', 'Cliente')->first();

        // Si el rol no existe, es un error de configuración, así que fallamos.
        if (!$clienteRole) {
            abort(500, "El rol 'Cliente' no se encuentra en la base de datos.");
        }

        // 3. Creamos el usuario con los datos y el role_id de 'Cliente'.
        $user = User::create([
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $clienteRole->id,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        // Añadimos validación también en la actualización.
        $request->validate([
            'nombre' => 'required',
            'apellidos' => 'required',
            'email' => 'required|email|unique:usuarios,email,' . $usuario->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $data = $request->except(['_token', '_method']);

        // Si se proporciona una nueva contraseña, la hasheamos.
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index');
    }

    // Mostrar un usuario específico
    public function show(User $usuario)
    {
        return view('usuarios.show', ['usuario' => $usuario]);
    }
}
