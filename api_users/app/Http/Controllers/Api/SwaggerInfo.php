<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API de Usuarios — Tienda Muebles',
    version: '1.0.0',
    description: 'API REST responsable de la autenticación y gestión de usuarios. Utiliza Laravel Sanctum para proteger rutas mediante Bearer tokens con abilities basadas en roles.',
    contact: new OA\Contact(email: 'admin@tienda-muebles.test'),
)]
#[OA\Server(url: 'http://127.0.0.1:8001', description: 'Servidor local')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token Bearer emitido por /api/login. Inclúyelo en la cabecera: Authorization: Bearer {token}',
)]
#[OA\Tag(name: 'Autenticación', description: 'Registro, login, logout y perfil del usuario autenticado')]
#[OA\Tag(name: 'Usuarios',      description: 'CRUD de usuarios — requiere rol Admin')]
#[OA\Tag(name: 'Roles',         description: 'Consulta de roles disponibles — requiere rol Admin')]
#[OA\Tag(name: 'Sesiones',      description: 'Verificación de sesión activa (uso interno entre servicios)')]
#[OA\Schema(
    schema: 'UserResponse',
    properties: [
        new OA\Property(property: 'id',       type: 'integer', example: 1),
        new OA\Property(property: 'name',     type: 'string',  example: 'Juan'),
        new OA\Property(property: 'surname',  type: 'string',  example: 'García'),
        new OA\Property(property: 'email',    type: 'string',  format: 'email', example: 'juan@example.com'),
        new OA\Property(property: 'rol_name', type: 'string',  example: 'Cliente'),
        new OA\Property(property: 'role_id',  type: 'integer', example: 2),
    ],
    type: 'object',
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The name field is required.'),
        new OA\Property(property: 'errors',  type: 'object'),
    ],
    type: 'object',
)]
class SwaggerInfo {}
