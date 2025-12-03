<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Email;

class RegisterController extends Controller
{
    // Mostrar formulario
    public function show()
    {
        return view('registro');
    }

    // Procesar registro
    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'name' => 'required|string|max:30',
            'surname' => 'required|string|max:30',
            'email' => 'required|string|email|max:80|unique:users',
            'password' => 'required|string|min:4|confirmed', // requiere campo password_confirmation
        ]);

        // Obtener rol de cliente
        // Buscamos por nombre o creamos si no existe
        $role = Role::firstOrCreate(['name' => 'Cliente']);

        // Crear Usuario en Base de Datos
        User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password), // ¡Contraseña cifrada!
            'role_id' => $role->id,
            'failed_attempts' => 0,
        ]);

        // Redirigir al login
        return redirect()->route('login.show')->with('success', 'Registro completado. Por favor inicia sesión.');
    }
}
