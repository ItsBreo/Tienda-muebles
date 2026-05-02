<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Models\Furniture;
use App\Models\Image;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    use ApiResponse;

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/furniture/{mueble}/gallery
    // Sube una o más imágenes a la galería de un mueble.
    // Body: multipart/form-data con campo "images[]"
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request, Furniture $mueble): JsonResponse
    {
        $request->validate([
            'images'       => 'required|array|min:1|max:10',
            'images.*'     => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'alt_text'     => 'nullable|string|max:255',
        ]);

        $uploaded = [];

        foreach ($request->file('images') as $index => $image) {
            $imageName = time() . '_' . uniqid() . '.' . $image->extension();
            $image->move(public_path('images'), $imageName);

            $img = $mueble->images()->create([
                'image_path'    => 'images/' . $imageName,
                'is_primary'    => false,
                'alt_text'      => $request->input('alt_text', $mueble->name . ' - imagen ' . ($index + 1)),
                'display_order' => $mueble->images()->max('display_order') + 1,
            ]);

            $uploaded[] = new ImageResource($img);
        }

        return $this->createdResponse(
            collect($uploaded)->map->resolve(request())->values(),
            count($uploaded) . ' imagen(es) subida(s) correctamente.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/furniture/{mueble}/gallery/{image}
    // Elimina una imagen de la galería (también la borra del disco).
    // ──────────────────────────────────────────────────────────────────────────
    public function destroy(Furniture $mueble, Image $image): JsonResponse
    {
        // Verificar que la imagen pertenece a este mueble
        if ($image->furniture_id !== $mueble->id) {
            return $this->forbiddenResponse('La imagen no pertenece a este mueble.');
        }

        // Borrar fichero del disco si existe
        $imagePath = public_path($image->image_path);
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        $wasPrimary = $image->is_primary;
        $image->delete();

        // Si era la principal, asignar la siguiente como principal
        if ($wasPrimary) {
            $siguiente = $mueble->images()->orderBy('display_order')->first();
            if ($siguiente) {
                $siguiente->update(['is_primary' => true]);
            }
        }

        return $this->messageResponse('Imagen eliminada correctamente.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/furniture/{mueble}/gallery/{image}/main
    // Establece una imagen de la galería como imagen principal del mueble.
    // ──────────────────────────────────────────────────────────────────────────
    public function setMain(Furniture $mueble, Image $image): JsonResponse
    {
        if ($image->furniture_id !== $mueble->id) {
            return $this->forbiddenResponse('La imagen no pertenece a este mueble.');
        }

        // Quitar el flag principal de todas y poner solo en la seleccionada
        $mueble->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return $this->okResponse(
            (new ImageResource($image->fresh()))->resolve(request()),
            'Imagen principal establecida correctamente.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUT /api/furniture/{mueble}/gallery/{image}/order
    // Actualiza el orden de visualización de una imagen.
    // ──────────────────────────────────────────────────────────────────────────
    public function updateOrder(Request $request, Furniture $mueble, Image $image): JsonResponse
    {
        if ($image->furniture_id !== $mueble->id) {
            return $this->forbiddenResponse('La imagen no pertenece a este mueble.');
        }

        $validated = $request->validate([
            'display_order' => 'required|integer|min:0|max:99',
        ]);

        $image->update($validated);

        return $this->okResponse(
            (new ImageResource($image->fresh()))->resolve(request()),
            'Orden de imagen actualizado.'
        );
    }
}
