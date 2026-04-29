<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Furniture;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GalleryController extends Controller
{
    /**
     * POST /api/furniture/{mueble}/gallery
     * Sube múltiples imágenes para la galería de un mueble.
     */
    public function store(Request $request, Furniture $mueble)
    {
        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $uploaded = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->extension();
                $image->move(public_path('images'), $imageName);

                $img = $mueble->images()->create([
                    'image_path' => 'images/' . $imageName,
                    'is_primary' => false,
                    'alt_text'   => $mueble->name . ' - Imagen galería',
                ]);

                $uploaded[] = $img;
            }
        }

        return response()->json([
            'message' => 'Imágenes subidas correctamente.',
            'images'  => $uploaded,
        ], 201);
    }

    /**
     * DELETE /api/furniture/{mueble}/gallery/{image}
     * Elimina una imagen de la galería.
     */
    public function destroy(Furniture $mueble, Image $image)
    {
        if ($image->furniture_id !== $mueble->id) {
            return response()->json(['message' => 'La imagen no pertenece a este mueble.'], 403);
        }

        $imagePath = public_path($image->image_path);
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        $image->delete();

        return response()->json(['message' => 'Imagen eliminada correctamente.'], 200);
    }

    /**
     * POST /api/furniture/{mueble}/gallery/{image}/main
     * Establece una imagen como principal.
     */
    public function setMain(Furniture $mueble, Image $image)
    {
        if ($image->furniture_id !== $mueble->id) {
            return response()->json(['message' => 'La imagen no pertenece a este mueble.'], 403);
        }

        $mueble->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json([
            'message' => 'Imagen principal establecida correctamente.',
            'image'   => $image->fresh(),
        ], 200);
    }
}
