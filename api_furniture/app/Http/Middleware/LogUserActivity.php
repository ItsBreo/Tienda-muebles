<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Get user from Sanctum remote_user attribute
        $user = $request->attributes->get('remote_user');
        $userId = $user ? $user['id'] : null;

        // Get current route name
        $routeName = $request->route()?->getName();

        // Map route names to human-readable actions
        $actionMap = [
            'furniture.store' => 'Crear Mueble',
            'furniture.update' => 'Editar Mueble',
            'furniture.destroy' => 'Eliminar Mueble',
            'categories.store' => 'Crear Categoría',
            'categories.update' => 'Editar Categoría',
            'categories.destroy' => 'Eliminar Categoría',
            'gallery.store' => 'Añadir Imagen a Galería',
            'gallery.destroy' => 'Eliminar Imagen de Galería',
            'gallery.setMain' => 'Establecer Imagen Principal',
        ];

        $action = $actionMap[$routeName] ?? $routeName;

        // Create activity log
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
        ]);

        return $response;
    }
}
