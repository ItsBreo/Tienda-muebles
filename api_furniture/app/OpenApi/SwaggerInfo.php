<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API de Muebles - Tienda Muebles',
    version: '1.0.0',
    description: 'API REST para catalogo, categorias, galeria y gestion de stock de muebles. Incluye endpoints publicos de consulta y endpoints protegidos para escritura.',
    contact: new OA\Contact(email: 'admin@tienda-muebles.test'),
)]
#[OA\Server(url: 'http://127.0.0.1:8000', description: 'Servidor local')]
#[OA\SecurityScheme(
    securityScheme: 'remoteAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Token Bearer validado por el middleware remote.auth contra el servicio de usuarios.',
)]
#[OA\Tag(name: 'Muebles', description: 'Consulta y gestion de muebles')]
#[OA\Tag(name: 'Categorias', description: 'Consulta y gestion de categorias')]
#[OA\Tag(name: 'Galeria', description: 'Gestion de imagenes de muebles')]
#[OA\Schema(
    schema: 'ImageResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12),
        new OA\Property(property: 'image_path', type: 'string', example: 'images/1762283911_690a51874b7da.png'),
        new OA\Property(property: 'is_primary', type: 'boolean', example: true),
        new OA\Property(property: 'display_order', type: 'integer', example: 0),
        new OA\Property(property: 'alt_text', type: 'string', example: 'Sofa confort - imagen principal'),
    ],
)]
#[OA\Schema(
    schema: 'CategoryResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Salon'),
        new OA\Property(property: 'description', type: 'string', example: 'Muebles para el salon'),
        new OA\Property(property: 'furniture_count', type: 'integer', nullable: true, example: 8),
    ],
)]
#[OA\Schema(
    schema: 'FurnitureListResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 4),
        new OA\Property(property: 'name', type: 'string', example: 'Sofa Confort'),
        new OA\Property(property: 'description', type: 'string', example: 'Sofa de tres plazas con tapizado premium'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 499.99),
        new OA\Property(property: 'price_eur', type: 'string', example: '499,99 €'),
        new OA\Property(property: 'stock', type: 'integer', example: 12),
        new OA\Property(property: 'main_color', type: 'string', example: 'Beige'),
        new OA\Property(property: 'is_salient', type: 'boolean', example: true),
        new OA\Property(property: 'main_image', type: 'string', example: 'images/sofa_confort_1.png'),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'category', ref: '#/components/schemas/CategoryResource'),
    ],
)]
#[OA\Schema(
    schema: 'FurnitureResource',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 4),
        new OA\Property(property: 'name', type: 'string', example: 'Sofa Confort'),
        new OA\Property(property: 'description', type: 'string', example: 'Sofa de tres plazas con tapizado premium'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 499.99),
        new OA\Property(property: 'price_eur', type: 'string', example: '499,99 €'),
        new OA\Property(property: 'stock', type: 'integer', example: 12),
        new OA\Property(property: 'materials', type: 'string', nullable: true, example: 'Madera y lino'),
        new OA\Property(property: 'dimensions', type: 'string', nullable: true, example: '220x95x90 cm'),
        new OA\Property(property: 'main_color', type: 'string', example: 'Beige'),
        new OA\Property(property: 'is_salient', type: 'boolean', example: true),
        new OA\Property(property: 'main_image', type: 'string', example: 'images/sofa_confort_1.png'),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'category', ref: '#/components/schemas/CategoryResource'),
        new OA\Property(
            property: 'images',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ImageResource')
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-05-08T21:29:28.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-05-09T10:15:00.000000Z'),
    ],
)]
#[OA\Schema(
    schema: 'PaginationMeta',
    type: 'object',
    properties: [
        new OA\Property(property: 'total', type: 'integer', example: 25),
        new OA\Property(property: 'per_page', type: 'integer', example: 12),
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 3),
        new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'to', type: 'integer', nullable: true, example: 12),
    ],
)]
#[OA\Schema(
    schema: 'ValidationError',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Los datos proporcionados no son validos.'),
        new OA\Property(property: 'errors', type: 'object'),
    ],
)]
#[OA\Schema(
    schema: 'MessageResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Operacion realizada correctamente.'),
    ],
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    type: 'object',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Ha ocurrido un error.'),
    ],
)]
class SwaggerInfo
{
}
