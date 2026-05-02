<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * FurnitureListResource — Versión ligera para el listado de muebles.
 * Usado en: GET /api/furniture
 * Solo incluye los campos necesarios para tarjetas de catálogo.
 */
class FurnitureListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mainImage = $this->resolveMainImage();
        $category  = $this->whenLoaded('category', fn() => new CategoryResource($this->category));

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => (float) $this->price,
            'price_eur'   => $this->getFormattedPrice('EUR'),
            'stock'       => (int) $this->stock,
            'main_color'  => $this->main_color,
            'is_salient'  => (bool) $this->is_salient,
            'main_image'  => $mainImage,
            'category_id' => $this->category_id,
            'category'    => $category,
        ];
    }

    /**
     * Resuelve la imagen principal desde la relación en memoria.
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
