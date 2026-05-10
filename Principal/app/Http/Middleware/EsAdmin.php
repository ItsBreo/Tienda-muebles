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

        if ($request->has('sesionId')) {
            $query = $request->query();
            unset($query['sesionId']);

            $target = $request->url();
            if (!empty($query)) {
                $target .= '?' . http_build_query($query);
            }

            return redirect()->to($target);
        }

        if (!$user) {
            return redirect()->route('login.show')
                ->withErrors(['errorCredenciales' => 'Debes iniciar sesión para acceder a esta sección.']);
        }

        if (!in_array($user['rol_name'] ?? '', ['Admin', 'Gestor'])) {
            return redirect()->route('principal')
                ->withErrors(['acceso' => 'No tienes permisos suficientes para acceder a esta sección.']);
        }

        return $next($request);
    }
}
