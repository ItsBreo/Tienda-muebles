<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/register
     * Registra un nuevo usuario con rol 'Cliente'.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:30',
            'surname'               => 'required|string|max:30',
            'email'                 => 'required|string|email|max:80|unique:users',
            'password'              => 'required|string|min:4|confirmed',
        ]);

        $role = Role::firstOrCreate(['name' => 'Cliente']);

        $user = User::create([
            'name'            => $data['name'],
            'surname'         => $data['surname'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'role_id'         => $role->id,
            'failed_attempts' => 0,
        ]);

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'user'    => $user->only(['id', 'name', 'surname', 'email', 'role_id']),
        ], 201);
    }

    /**
     * POST /api/login
     * Autentica un usuario y devuelve sus datos junto a la sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('role')->where('email', $credentials['email'])->first();

        // Usuario no existe
        if (!$user) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Cuenta bloqueada
        if ($user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
            $minutosRestantes = Carbon::now()->diffInMinutes($user->locked_until) + 1;
            return response()->json([
                'message' => "Cuenta bloqueada temporalmente. Inténtalo de nuevo en {$minutosRestantes} minutos.",
            ], 403);
        }

        // Contraseña incorrecta
        if (!Hash::check($credentials['password'], $user->password)) {
            $user->increment('failed_attempts');

            if ($user->failed_attempts >= 3) {
                $user->update([
                    'locked_until'    => Carbon::now()->addMinutes(5),
                    'failed_attempts' => 0,
                ]);
                return response()->json([
                    'message' => 'Has excedido el número de intentos. Tu cuenta ha sido bloqueada por 5 minutos.',
                ], 403);
            }

            $intentosRestantes = 3 - $user->failed_attempts;
            return response()->json([
                'message' => "Contraseña incorrecta. Te quedan {$intentosRestantes} intentos.",
            ], 401);
        }

        // Login exitoso: resetear contadores
        $user->update([
            'failed_attempts' => 0,
            'locked_until'    => null,
            'last_login_at'   => Carbon::now(),
        ]);

        // Registrar log de sesión
        $log = SessionLog::create([
            'session_id' => uniqid('sess_', true),
            'user_id'    => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at'   => Carbon::now(),
        ]);

        return response()->json([
            'message'    => 'Login correcto.',
            'session_id' => $log->session_id,
            'user'       => [
                'id'       => $user->id,
                'name'     => $user->name,
                'surname'  => $user->surname,
                'email'    => $user->email,
                'rol_name' => $user->role ? $user->role->name : 'Cliente',
                'role_id'  => $user->role_id,
            ],
        ], 200);
    }

    /**
     * POST /api/logout
     * Cierra la sesión registrando el logout_at en el log.
     */
    public function logout(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $log = SessionLog::where('session_id', $request->session_id)
            ->whereNull('logout_at')
            ->first();

        if ($log) {
            $log->update(['logout_at' => Carbon::now()]);
        }

        return response()->json(['message' => 'Sesión cerrada correctamente.'], 200);
    }

    /**
     * GET /api/session/{session_id}
     * Valida un session_id y devuelve los datos del usuario activo.
     * Usado por Principal para resolver qué usuario tiene cada sesión.
     */
    public function sessionCheck(Request $request, string $sessionId)
    {
        $log = SessionLog::where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->with('user.role')
            ->first();

        if (!$log || !$log->user) {
            return response()->json(['message' => 'Sesión no válida o expirada.'], 404);
        }

        $user = $log->user;

        return response()->json([
            'id'       => $user->id,
            'name'     => $user->name,
            'surname'  => $user->surname,
            'email'    => $user->email,
            'rol_name' => $user->role ? $user->role->name : 'Cliente',
            'role_id'  => $user->role_id,
        ], 200);
    }
}
