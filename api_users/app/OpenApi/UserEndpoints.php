<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/users',
    operationId: 'userIndex',
    summary: 'Listar todos los usuarios',
    description: 'Devuelve todos los usuarios con su rol, ordenados por ultimo login. Requiere ability **users:list** (rol Admin).',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Lista de usuarios',
            content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/UserResponse')),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado - se requiere rol Admin'),
    ],
)]
#[OA\Post(
    path: '/api/users',
    operationId: 'userStore',
    summary: 'Crear un nuevo usuario',
    description: 'Crea un usuario con el rol especificado. Uso administrativo. Requiere ability **users:create** (rol Admin).',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'role_id'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Maria'),
                new OA\Property(property: 'surname', type: 'string', example: 'Lopez'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 4, example: 'secret'),
                new OA\Property(property: 'role_id', type: 'integer', example: 2),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Usuario creado correctamente',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Usuario creado correctamente.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserResponse'),
            ]),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
        new OA\Response(response: 422, description: 'Error de validacion', ref: '#/components/schemas/ValidationError'),
    ],
)]
#[OA\Get(
    path: '/api/users/{id}',
    operationId: 'userShow',
    summary: 'Obtener un usuario por ID',
    description: 'Devuelve los datos de un usuario. Requiere ability **users:view** (Admin) o ser el propio usuario.',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del usuario', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Datos del usuario', content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
        new OA\Response(response: 404, description: 'Usuario no encontrado'),
    ],
)]
#[OA\Put(
    path: '/api/users/{id}',
    operationId: 'userUpdate',
    summary: 'Actualizar un usuario',
    description: 'Admin puede cambiar cualquier campo incluido el rol; un usuario con **profile:update** solo puede editar sus propios datos sin cambiar el rol.',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del usuario', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Juan'),
                new OA\Property(property: 'surname', type: 'string', example: 'Garcia'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 4, example: 'newpassword'),
                new OA\Property(property: 'role_id', type: 'integer', example: 1, description: 'Solo Admin puede modificarlo'),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Usuario actualizado correctamente',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Usuario actualizado correctamente.'),
                new OA\Property(property: 'user', ref: '#/components/schemas/UserResponse'),
            ]),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
        new OA\Response(response: 404, description: 'Usuario no encontrado'),
        new OA\Response(response: 422, description: 'Error de validacion', ref: '#/components/schemas/ValidationError'),
    ],
)]
#[OA\Delete(
    path: '/api/users/{id}',
    operationId: 'userDestroy',
    summary: 'Eliminar un usuario',
    description: 'Elimina un usuario por ID. Requiere ability **users:delete** (rol Admin).',
    security: [['sanctum' => []]],
    tags: ['Usuarios'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del usuario', schema: new OA\Schema(type: 'integer', example: 1)),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Usuario eliminado correctamente',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Usuario eliminado correctamente.'),
            ]),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
        new OA\Response(response: 404, description: 'Usuario no encontrado'),
    ],
)]
#[OA\Get(
    path: '/api/roles',
    operationId: 'roleIndex',
    summary: 'Listar todos los roles',
    description: 'Devuelve todos los roles disponibles en el sistema. Requiere ability **roles:list** (rol Admin).',
    security: [['sanctum' => []]],
    tags: ['Roles'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Lista de roles',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Admin'),
                    ],
                    type: 'object',
                ),
            ),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
    ],
)]
class UserEndpoints
{
}
