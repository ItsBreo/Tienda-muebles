<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * FurnitureResource — Detalle completo de un mueble.
 * Usado en: GET /api/furniture/{id}
 * Incluye todas las imágenes, categoría y campos calculados.
 */
class FurnitureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Usamos la relación ya cargada (sin nueva query)
        $images       = $this->whenLoaded('images', fn() => ImageResource::collection($this->images));
        $category     = $this->whenLoaded('category', fn() => new CategoryResource($this->category));
        $mainImage    = $this->resolveMainImage();

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'price'         => (float) $this->price,
            'price_eur'     => $this->getFormattedPrice('EUR'),
            'stock'         => (int) $this->stock,
            'materials'     => $this->materials,
            'dimensions'    => $this->dimensions,
            'main_color'    => $this->main_color,
            'is_salient'    => (bool) $this->is_salient,
            'main_image'    => $mainImage,
            'category_id'   => $this->category_id,
            'category'      => $category,
            'images'        => $images,
            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Resuelve la imagen principal usando la relación ya cargada en memoria
     * (evita N+1 queries).
     */
    private function resolveMainImage(): string
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            $primary = $this->images->firstWhere('is_primary', true);
            return $primary ? $primary->image_path : $this->images->first()->image_path;
        }

        return 'images/default.png';
    }
}
