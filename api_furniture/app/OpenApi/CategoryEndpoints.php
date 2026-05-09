<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/categories',
    operationId: 'categoryIndex',
    summary: 'Listar categorias',
    description: 'Devuelve todas las categorias disponibles con el numero de muebles asociado.',
    tags: ['Categorias'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Categorias disponibles',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Categorias disponibles.'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CategoryResource')),
                ],
                type: 'object',
            ),
        ),
    ],
)]
#[OA\Get(
    path: '/api/categories/{category}',
    operationId: 'categoryShow',
    summary: 'Obtener categoria',
    description: 'Devuelve el detalle de una categoria junto con su listado de muebles.',
    tags: ['Categorias'],
    parameters: [
        new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Detalle de categoria',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Detalle de categoria.'),
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Salon'),
                            new OA\Property(property: 'description', type: 'string', example: 'Muebles para el salon'),
                            new OA\Property(property: 'furniture_count', type: 'integer', example: 8),
                            new OA\Property(property: 'furniture', type: 'array', items: new OA\Items(ref: '#/components/schemas/FurnitureListResource')),
                        ],
                        type: 'object',
                    ),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 404, description: 'Categoria no encontrada'),
    ],
)]
#[OA\Post(
    path: '/api/categories',
    operationId: 'categoryStore',
    summary: 'Crear categoria',
    description: 'Crea una nueva categoria.',
    security: [['remoteAuth' => []]],
    tags: ['Categorias'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Despacho'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Muebles para oficina en casa'),
            ],
            type: 'object',
        ),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Categoria creada',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Categoría creada correctamente.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/CategoryResource'),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
        new OA\Response(response: 422, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Put(
    path: '/api/categories/{category}',
    operationId: 'categoryUpdate',
    summary: 'Actualizar categoria',
    description: 'Actualiza una categoria existente.',
    security: [['remoteAuth' => []]],
    tags: ['Categorias'],
    parameters: [
        new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Dormitorio'),
                new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Muebles de dormitorio'),
            ],
            type: 'object',
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Categoria actualizada',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Categoría actualizada correctamente.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/CategoryResource'),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
        new OA\Response(response: 422, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Delete(
    path: '/api/categories/{category}',
    operationId: 'categoryDestroy',
    summary: 'Eliminar categoria',
    description: 'Elimina una categoria. Los muebles relacionados quedan sin categoria segun la restriccion de base de datos.',
    security: [['remoteAuth' => []]],
    tags: ['Categorias'],
    parameters: [
        new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Categoria eliminada', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
    ],
)]
class CategoryEndpoints
{
}
