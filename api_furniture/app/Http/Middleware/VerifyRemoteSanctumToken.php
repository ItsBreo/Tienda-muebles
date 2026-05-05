<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifyRemoteSanctumToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$abilities
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$abilities)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado. Token no proporcionado.'
            ], 401);
        }

        $apiUrl = env('API_USERS_URL', 'http://api_users.test');
        
        try {
            $response = Http::withToken($token)->get($apiUrl . '/api/profile');

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado. Token inválido o expirado.'
                ], 401);
            }

            $data = $response->json();
            
            // Compatibilidad con el formato ApiResponse
            if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
                $user = $data['data'];
            } else {
                $user = $data;
            }

            $userAbilities = $user['abilities'] ?? [];

            // Verificación de habilidades requeridas
            if (!empty($abilities)) {
                $hasAbilities = true;
                foreach ($abilities as $ability) {
                    if (!in_array($ability, $userAbilities) && !in_array('admin.panel', $userAbilities)) {
                        $hasAbilities = false;
                        break;
                    }
                }
                
                if (!$hasAbilities) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Acceso denegado. Faltan permisos para realizar esta acción.'
                    ], 403);
                }
            }

            // Inyectamos el usuario en los atributos de la request
            $request->attributes->set('remote_user', $user);

        } catch (\Exception $e) {
            Log::error('Error al conectar con api_users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al validar token.'
            ], 500);
        }

        return $next($request);
    }
}
