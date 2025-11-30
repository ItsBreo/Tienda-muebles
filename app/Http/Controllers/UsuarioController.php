<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        // Traemos todos los usuarios, los más recientes primero
        $users = User::orderByDesc('last_login_at')->get();

        return view('admin.usuarios.index', compact('users'));
    }

    // TODO: Añadir más métodos si es necesario (create, store, edit, update, destroy);
}
