<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            // Solo se incluye si se cargó withCount('furniture')
            'furniture_count' => $this->whenCounted('furniture'),
            // Solo se incluye si se cargó la relación furniture
            'furniture'      => $this->whenLoaded(
                'furniture',
                fn() => FurnitureListResource::collection($this->furniture)
            ),
        ];
    }
}
