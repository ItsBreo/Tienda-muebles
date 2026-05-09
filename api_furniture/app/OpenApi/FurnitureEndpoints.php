<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/furniture',
    operationId: 'furnitureIndex',
    summary: 'Listar muebles',
    description: 'Devuelve un listado paginado de muebles con filtros, ordenacion y metadatos de paginacion.',
    tags: ['Muebles'],
    parameters: [
        new OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'q', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'min_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
        new OA\Parameter(name: 'max_price', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
        new OA\Parameter(name: 'color', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'only_salient', in: 'query', required: false, schema: new OA\Schema(type: 'boolean')),
        new OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['price_asc', 'price_desc', 'name_asc', 'name_desc', 'date_new', 'date_old'])),
        new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', enum: [6, 12, 24, 48])),
        new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Listado paginado',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FurnitureListResource')),
                    new OA\Property(property: 'pagination', ref: '#/components/schemas/PaginationMeta'),
                    new OA\Property(property: 'filters', type: 'object'),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 422, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
#[OA\Get(
    path: '/api/furniture/featured',
    operationId: 'furnitureFeatured',
    summary: 'Listar muebles destacados',
    description: 'Devuelve muebles destacados. Permite limitar el numero de resultados.',
    tags: ['Muebles'],
    parameters: [
        new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 24)),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Muebles destacados',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Muebles destacados.'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/FurnitureListResource')),
                ],
                type: 'object',
            ),
        ),
    ],
)]
#[OA\Get(
    path: '/api/furniture/colors',
    operationId: 'furnitureColors',
    summary: 'Listar colores disponibles',
    description: 'Devuelve la lista unica de colores disponibles para filtros del catalogo.',
    tags: ['Muebles'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Colores disponibles',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Colores disponibles.'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string')),
                ],
                type: 'object',
            ),
        ),
    ],
)]
#[OA\Get(
    path: '/api/furniture/{furniture}',
    operationId: 'furnitureShow',
    summary: 'Obtener detalle de un mueble',
    description: 'Devuelve el detalle completo de un mueble, incluyendo categoria e imagenes.',
    tags: ['Muebles'],
    parameters: [
        new OA\Parameter(name: 'furniture', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Detalle del mueble',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Detalle del mueble.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/FurnitureResource'),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 404, description: 'Mueble no encontrado'),
    ],
)]
#[OA\Post(
    path: '/api/furniture',
    operationId: 'furnitureStore',
    summary: 'Crear mueble',
    description: 'Crea un nuevo mueble. Requiere token con permiso de escritura.',
    security: [['remoteAuth' => []]],
    tags: ['Muebles'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['name', 'description', 'price', 'stock', 'category_id', 'main_color'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'price', type: 'number', format: 'float'),
                    new OA\Property(property: 'stock', type: 'integer'),
                    new OA\Property(property: 'category_id', type: 'integer'),
                    new OA\Property(property: 'materials', type: 'string', nullable: true),
                    new OA\Property(property: 'dimensions', type: 'string', nullable: true),
                    new OA\Property(property: 'main_color', type: 'string'),
                    new OA\Property(property: 'is_salient', type: 'boolean'),
                    new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                ],
                type: 'object',
            ),
        ),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Mueble creado',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Mueble creado correctamente.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/FurnitureResource'),
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
    path: '/api/furniture/{furniture}',
    operationId: 'furnitureUpdate',
    summary: 'Actualizar mueble',
    description: 'Actualiza un mueble existente. Requiere token con permiso de edicion.',
    security: [['remoteAuth' => []]],
    tags: ['Muebles'],
    parameters: [
        new OA\Parameter(name: 'furniture', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['name', 'description', 'price', 'stock', 'category_id', 'main_color'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'price', type: 'number', format: 'float'),
                    new OA\Property(property: 'stock', type: 'integer'),
                    new OA\Property(property: 'category_id', type: 'integer'),
                    new OA\Property(property: 'materials', type: 'string', nullable: true),
                    new OA\Property(property: 'dimensions', type: 'string', nullable: true),
                    new OA\Property(property: 'main_color', type: 'string'),
                    new OA\Property(property: 'is_salient', type: 'boolean'),
                    new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                ],
                type: 'object',
            ),
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Mueble actualizado',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Mueble actualizado correctamente.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/FurnitureResource'),
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
    path: '/api/furniture/{furniture}',
    operationId: 'furnitureDestroy',
    summary: 'Eliminar mueble',
    description: 'Elimina un mueble y sus imagenes asociadas.',
    security: [['remoteAuth' => []]],
    tags: ['Muebles'],
    parameters: [
        new OA\Parameter(name: 'furniture', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Mueble eliminado', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        new OA\Response(response: 401, description: 'No autenticado'),
        new OA\Response(response: 403, description: 'No autorizado'),
    ],
)]
#[OA\Post(
    path: '/api/furniture/decrement-stock',
    operationId: 'furnitureDecrementStock',
    summary: 'Descontar stock',
    description: 'Descuenta stock de varios muebles en una sola operacion.',
    tags: ['Muebles'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['items'],
            properties: [
                new OA\Property(
                    property: 'items',
                    type: 'array',
                    items: new OA\Items(
                        required: ['producto_id', 'cantidad'],
                        properties: [
                            new OA\Property(property: 'producto_id', type: 'integer', example: 4),
                            new OA\Property(property: 'cantidad', type: 'integer', example: 2),
                        ],
                        type: 'object',
                    ),
                ),
            ],
            type: 'object',
        ),
    ),
    responses: [
        new OA\Response(response: 200, description: 'Stock actualizado', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        new OA\Response(response: 400, description: 'Stock insuficiente', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        new OA\Response(response: 422, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
class FurnitureEndpoints
{
}
