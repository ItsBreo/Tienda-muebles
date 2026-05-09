<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/register',
    operationId: 'authRegister',
    summary: 'Registrar un nuevo usuario',
    description: "Crea un usuario con rol 'Cliente'. No requiere autenticacion.",
    tags: ['Autenticación'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'surname', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 30, example: 'Juan'),
                new OA\Property(property: 'surname', type: 'string', maxLength: 30, example: 'Garcia'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 4, example: 'secret'),
                new OA\Property(property: 'password_confirmation', type: 'string', example: 'secret'),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Usuario registrado correctamente',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Usuario registrado correctamente.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserResponse'),
            ]),
        ),
        new OA\Response(response: 422, description: 'Error de validacion', ref: '#/components/schemas/ValidationError'),
    ],
)]
#[OA\Post(
    path: '/api/login',
    operationId: 'authLogin',
    summary: 'Iniciar sesion',
    description: 'Autentica al usuario y devuelve un Bearer token Sanctum con abilities segun el rol. Rol **Admin** obtiene abilities de gestion de usuarios; cualquier otro rol obtiene abilities de perfil.',
    tags: ['Autenticación'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'secret'),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Login correcto',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Login correcto.'),
                new OA\Property(property: 'token', type: 'string', example: '1|abc123xyz...'),
                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                new OA\Property(property: 'abilities', type: 'array', items: new OA\Items(type: 'string'), example: ['profile:view', 'profile:update']),
                new OA\Property(property: 'session_id', type: 'string', example: 'sess_64f0a1b2c3'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserResponse'),
            ]),
        ),
        new OA\Response(
            response: 401,
            description: 'Credenciales incorrectas',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Credenciales incorrectas.'),
            ]),
        ),
        new OA\Response(
            response: 403,
            description: 'Cuenta bloqueada temporalmente',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Cuenta bloqueada temporalmente. Intentalo de nuevo en 5 minutos.'),
            ]),
        ),
    ],
)]
#[OA\Post(
    path: '/api/logout',
    operationId: 'authLogout',
    summary: 'Cerrar sesion',
    description: 'Revoca el token Bearer actual y cierra el log de sesion. Requiere autenticacion.',
    security: [['sanctum' => []]],
    tags: ['Autenticación'],
    requestBody: new OA\RequestBody(
        required: false,
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'session_id', type: 'string', example: 'sess_64f0a...', description: 'Opcional - cierra tambien el log de sesion correspondiente'),
        ]),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Sesion cerrada correctamente',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Sesion cerrada correctamente.'),
            ]),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
    ],
)]
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
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Juan'),
                new OA\Property(property: 'surname', type: 'string', example: 'Garcia'),
                new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
                new OA\Property(property: 'rol_name', type: 'string', example: 'Cliente'),
                new OA\Property(property: 'role_id', type: 'integer', example: 2),
                new OA\Property(property: 'abilities', type: 'array', items: new OA\Items(type: 'string'), example: ['profile:view', 'profile:update']),
            ]),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
    ],
)]
#[OA\Get(
    path: '/api/session/{session_id}',
    operationId: 'sessionCheck',
    summary: 'Verificar sesion activa',
    description: 'Valida un session_id y devuelve los datos del usuario. Endpoint publico para consumo interno entre microservicios.',
    tags: ['Sesiones'],
    parameters: [
        new OA\Parameter(
            name: 'session_id',
            in: 'path',
            required: true,
            description: 'ID de sesion generado en el login',
            schema: new OA\Schema(type: 'string', example: 'sess_64f0a1b2c3d4e'),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Sesion valida - datos del usuario',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
        ),
        new OA\Response(
            response: 404,
            description: 'Sesion no valida o expirada',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Sesion no valida o expirada.'),
            ]),
        ),
    ],
)]
class AuthEndpoints
{
}
