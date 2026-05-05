<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionLog;
use App\Models\User;
use App\Models\Role;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponse;

    #[OA\Post(
        path: '/api/register',
        operationId: 'authRegister',
        summary: 'Registrar un nuevo usuario',
        description: "Crea un usuario con rol 'Cliente'. No requiere autenticación.",
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'surname', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name',                  type: 'string',  maxLength: 30,  example: 'Juan'),
                    new OA\Property(property: 'surname',               type: 'string',  maxLength: 30,  example: 'García'),
                    new OA\Property(property: 'email',                 type: 'string',  format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'password',              type: 'string',  minLength: 4,  example: 'secret'),
                    new OA\Property(property: 'password_confirmation', type: 'string',  example: 'secret'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado correctamente',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Usuario registrado correctamente.'),
                    new OA\Property(property: 'user',    ref: '#/components/schemas/UserResponse'),
                ]),
            ),
            new OA\Response(response: 422, description: 'Error de validación', ref: '#/components/schemas/ValidationError'),
        ],
    )]
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

        return $this->successResponse(
            ['user' => $user->only(['id', 'name', 'surname', 'email', 'role_id'])],
            'Usuario registrado correctamente.',
            201
        );
    }

    #[OA\Post(
        path: '/api/login',
        operationId: 'authLogin',
        summary: 'Iniciar sesión',
        description: 'Autentica al usuario y devuelve un Bearer token Sanctum con abilities según el rol. Rol **Admin** obtiene abilities de gestión de usuarios; cualquier otro rol obtiene abilities de perfil.',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'secret'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login correcto',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message',    type: 'string', example: 'Login correcto.'),
                    new OA\Property(property: 'token',      type: 'string', example: '1|abc123xyz...'),
                    new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                    new OA\Property(property: 'abilities',  type: 'array',  items: new OA\Items(type: 'string'), example: ['profile:view', 'profile:update']),
                    new OA\Property(property: 'session_id', type: 'string', example: 'sess_64f0a1b2c3'),
                    new OA\Property(property: 'user',       ref: '#/components/schemas/UserResponse'),
                ]),
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales incorrectas',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Credenciales incorrectas.')]),
            ),
            new OA\Response(
                response: 403,
                description: 'Cuenta bloqueada temporalmente',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Cuenta bloqueada temporalmente. Inténtalo de nuevo en 5 minutos.')]),
            ),
        ],
    )]
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
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
                    'locked_until'    => Carbon::now()->addMinutes(5),
                    'failed_attempts' => 0,
                ]);
                return $this->errorResponse('Has excedido el número de intentos. Tu cuenta ha sido bloqueada por 5 minutos.', 403);
            }

            $intentosRestantes = 3 - $user->failed_attempts;
            return $this->errorResponse("Contraseña incorrecta. Te quedan {$intentosRestantes} intentos.", 401);
        }

        $user->update([
            'failed_attempts' => 0,
            'locked_until'    => null,
            'last_login_at'   => Carbon::now(),
        ]);

        $log = SessionLog::create([
            'session_id' => uniqid('sess_', true),
            'user_id'    => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at'   => Carbon::now(),
        ]);

        $abilities = $this->abilitiesForRole($user->role?->name);
        $token = $user->createToken('auth-token', $abilities)->plainTextToken;

        return $this->successResponse([
            'token'      => $token,
            'token_type' => 'Bearer',
            'abilities'  => $abilities,
            'session_id' => $log->session_id,
            'user'       => [
                'id'       => $user->id,
                'name'     => $user->name,
                'surname'  => $user->surname,
                'email'    => $user->email,
                'rol_name' => $user->role?->name ?? 'Cliente',
                'role_id'  => $user->role_id,
            ],
        ], 'Login correcto.');
    }

    #[OA\Post(
        path: '/api/logout',
        operationId: 'authLogout',
        summary: 'Cerrar sesión',
        description: 'Revoca el token Bearer actual y cierra el log de sesión. Requiere autenticación.',
        security: [['sanctum' => []]],
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'session_id', type: 'string', example: 'sess_64f0a...', description: 'Opcional — cierra también el log de sesión correspondiente'),
            ]),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada correctamente',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada correctamente.')]),
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ],
    )]
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

    #[OA\Get(
        path: '/api/profile',
        operationId: 'authProfile',
        summary: 'Obtener perfil del usuario autenticado',
        description: 'Devuelve los datos del usuario cuyo token Bearer se incluye en la cabecera, junto a las abilities del token.',
        security: [['sanctum' => []]],
        tags: ['Autenticación'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Perfil del usuario autenticado',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'id',        type: 'integer', example: 1),
                    new OA\Property(property: 'name',      type: 'string',  example: 'Juan'),
                    new OA\Property(property: 'surname',   type: 'string',  example: 'García'),
                    new OA\Property(property: 'email',     type: 'string',  example: 'juan@example.com'),
                    new OA\Property(property: 'rol_name',  type: 'string',  example: 'Cliente'),
                    new OA\Property(property: 'role_id',   type: 'integer', example: 2),
                    new OA\Property(property: 'abilities', type: 'array',   items: new OA\Items(type: 'string'), example: ['profile:view', 'profile:update']),
                ]),
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ],
    )]
    public function profile(Request $request)
    {
        $user = $request->user()->load('role');

        return $this->successResponse([
            'id'        => $user->id,
            'name'      => $user->name,
            'surname'   => $user->surname,
            'email'     => $user->email,
            'rol_name'  => $user->role?->name ?? 'Cliente',
            'role_id'   => $user->role_id,
            'abilities' => $request->user()->currentAccessToken()->abilities,
        ], 'Perfil recuperado correctamente.');
    }

    #[OA\Get(
        path: '/api/session/{session_id}',
        operationId: 'sessionCheck',
        summary: 'Verificar sesión activa',
        description: 'Valida un session_id y devuelve los datos del usuario. Endpoint público para consumo interno entre microservicios.',
        tags: ['Sesiones'],
        parameters: [
            new OA\Parameter(
                name: 'session_id',
                in: 'path',
                required: true,
                description: 'ID de sesión generado en el login',
                schema: new OA\Schema(type: 'string', example: 'sess_64f0a1b2c3d4e'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión válida — datos del usuario',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Sesión no válida o expirada',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string', example: 'Sesión no válida o expirada.')]),
            ),
        ],
    )]
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
            'id'       => $user->id,
            'name'     => $user->name,
            'surname'  => $user->surname,
            'email'    => $user->email,
            'rol_name' => $user->role?->name ?? 'Cliente',
            'role_id'  => $user->role_id,
        ], 'Sesión válida.');
    }

    // -------------------------------------------------------------------------

    private function abilitiesForRole(?string $roleName): array
    {
        return match ($roleName) {
            'Admin' => ['perfil.ver', 'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar', 'admin.panel'],
            'Gestor' => ['perfil.ver', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar'],
            default => ['perfil.ver', 'muebles.ver', 'carrito.gestionar', 'pedidos.crear'],
        };
    }
}
