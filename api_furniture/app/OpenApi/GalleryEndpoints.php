<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/furniture/{mueble}/gallery',
    operationId: 'galleryStore',
    summary: 'Subir imagenes a la galeria',
    description: 'Sube una o varias imagenes a la galeria de un mueble.',
    security: [['remoteAuth' => []]],
    tags: ['Galeria'],
    parameters: [
        new OA\Parameter(name: 'mueble', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['images'],
                properties: [
                    new OA\Property(
                        property: 'images',
                        type: 'array',
                        items: new OA\Items(type: 'string', format: 'binary')
                    ),
                    new OA\Property(property: 'alt_text', type: 'string', nullable: true),
                ],
                type: 'object',
            ),
        ),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Imagenes subidas',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: '2 imagen(es) subida(s) correctamente.'),
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ImageResource')),
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
    path: '/api/furniture/{mueble}/gallery/{image}',
    operationId: 'galleryDestroy',
    summary: 'Eliminar imagen de galeria',
    description: 'Elimina una imagen de la galeria y, si era principal, reasigna la siguiente.',
    security: [['remoteAuth' => []]],
    tags: ['Galeria'],
    parameters: [
        new OA\Parameter(name: 'mueble', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'image', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(response: 200, description: 'Imagen eliminada', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        new OA\Response(response: 403, description: 'La imagen no pertenece a este mueble', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
    ],
)]
#[OA\Post(
    path: '/api/furniture/{mueble}/gallery/{image}/main',
    operationId: 'gallerySetMain',
    summary: 'Marcar imagen principal',
    description: 'Establece una imagen existente como principal del mueble.',
    security: [['remoteAuth' => []]],
    tags: ['Galeria'],
    parameters: [
        new OA\Parameter(name: 'mueble', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'image', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Imagen principal actualizada',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Imagen principal establecida correctamente.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/ImageResource'),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 403, description: 'La imagen no pertenece a este mueble', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
    ],
)]
#[OA\Put(
    path: '/api/furniture/{mueble}/gallery/{image}/order',
    operationId: 'galleryUpdateOrder',
    summary: 'Actualizar orden de imagen',
    description: 'Cambia el orden de visualizacion de una imagen de galeria.',
    security: [['remoteAuth' => []]],
    tags: ['Galeria'],
    parameters: [
        new OA\Parameter(name: 'mueble', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'image', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['display_order'],
            properties: [
                new OA\Property(property: 'display_order', type: 'integer', example: 2),
            ],
            type: 'object',
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Orden actualizado',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                    new OA\Property(property: 'message', type: 'string', example: 'Orden de imagen actualizado.'),
                    new OA\Property(property: 'data', ref: '#/components/schemas/ImageResource'),
                ],
                type: 'object',
            ),
        ),
        new OA\Response(response: 403, description: 'La imagen no pertenece a este mueble', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        new OA\Response(response: 422, description: 'Error de validacion', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
    ],
)]
class GalleryEndpoints
{
}
