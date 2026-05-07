<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EsAdmin
{
    /**
     * Solo permite el acceso si el usuario en sesión tiene rol "Admin".
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Session::get('user');

        if (!$user) {
            return redirect()->route('login.show')
                ->withErrors(['errorCredenciales' => 'Debes iniciar sesión para acceder a esta sección.']);
        }

        if (($user['rol_name'] ?? '') !== 'Admin') {
            return redirect()->route('principal')
                ->withErrors(['acceso' => 'No tienes permisos para acceder al panel de administración.']);
        }

        return $next($request);
    }
}
