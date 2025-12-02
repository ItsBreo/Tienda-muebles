<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UsuarioController extends Controller
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

    public function index(Request $request)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Si pasamos checkAdmin, el sesionId debe estar presente
        $sesionId = $request->query('sesionId') ?? $request->cookie('current_sesionId');

        // Traemos todos los usuarios, los más recientes primero
        $users = User::orderByDesc('last_login_at')->get();

        return view('admin.usuarios.index', compact('users', 'sesionId'));
    }

    // TODO: Añadir más métodos si es necesario (create, store, edit, update, destroy);
}
