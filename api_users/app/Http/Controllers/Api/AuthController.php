<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SessionLog;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:30',
            'surname' => 'required|string|max:30',
            'email' => 'required|string|email|max:80|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $role = Role::firstOrCreate(['name' => 'Cliente']);

        $user = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'failed_attempts' => 0,
        ]);

        return $this->successResponse(
            ['user' => $user->only(['id', 'name', 'surname', 'email', 'role_id'])],
            'Usuario registrado correctamente.',
            201
        );
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('role')->where('email', $credentials['email'])->first();

        if (!$user) {
            return $this->errorResponse('Credenciales incorrectas.', 401);
        }

        if ($user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
            $minutosRestantes = Carbon::now()->diffInMinutes($user->locked_until) + 1;
            return $this->errorResponse("Cuenta bloqueada temporalmente. Inténtalo de nuevo en {$minutosRestantes} minutos.", 403);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            $user->increment('failed_attempts');

            if ($user->failed_attempts >= 3) {
                $user->update([
                    'locked_until' => Carbon::now()->addMinutes(5),
                    'failed_attempts' => 0,
                ]);
                return $this->errorResponse('Has excedido el número de intentos. Tu cuenta ha sido bloqueada por 5 minutos.', 403);
            }

            $intentosRestantes = 3 - $user->failed_attempts;
            return $this->errorResponse("Contraseña incorrecta. Te quedan {$intentosRestantes} intentos.", 401);
        }

        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => Carbon::now(),
        ]);

        $log = SessionLog::create([
            'session_id' => uniqid('sess_', true),
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => Carbon::now(),
        ]);

        $abilities = $this->abilitiesForRole($user->role?->name);
        $token = $user->createToken('auth-token', $abilities)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'session_id' => $log->session_id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'rol_name' => $user->role?->name ?? 'Cliente',
                'role_id' => $user->role_id,
            ],
        ], 'Login correcto.');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $sessionId = $request->input('session_id');
        if ($sessionId) {
            SessionLog::where('session_id', $sessionId)
                ->whereNull('logout_at')
                ->update(['logout_at' => Carbon::now()]);
        }

        return $this->successResponse(null, 'Sesión cerrada correctamente.');
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('role');

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'rol_name' => $user->role?->name ?? 'Cliente',
            'role_id' => $user->role_id,
            'abilities' => $request->user()->currentAccessToken()->abilities,
        ], 'Perfil recuperado correctamente.');
    }

    public function sessionCheck(Request $request, string $sessionId)
    {
        $log = SessionLog::where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->with('user.role')
            ->first();

        if (!$log || !$log->user) {
            return $this->errorResponse('Sesión no válida o expirada.', 404);
        }

        $user = $log->user;

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'surname' => $user->surname,
            'email' => $user->email,
            'rol_name' => $user->role?->name ?? 'Cliente',
            'role_id' => $user->role_id,
        ], 'Sesión válida.');
    }

    private function abilitiesForRole(?string $roleName): array
    {
        return match ($roleName) {
            'Admin' => ['perfil.ver', 'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar', 'admin.panel'],
            'Gestor' => ['perfil.ver', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar'],
            default => ['perfil.ver', 'muebles.ver', 'carrito.gestionar', 'pedidos.crear'],
        };
    }
}
