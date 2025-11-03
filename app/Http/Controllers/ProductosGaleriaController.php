<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;

class ProductosGaleriaController extends Controller {
    /**
     * Sube y guarda las imágenes para un mueble específico.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        // Usamos el modelo para encontrar el mueble directamente en la base de datos.
        $mueble = Furniture::find($id);
        if (!$mueble) {
            return back()->with('error', 'Mueble no encontrado.');
        }
        // Asumiendo que 'images' es una columna de tipo JSON en tu tabla de muebles.
        $imagePaths = $mueble->images ?? [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Genera un nombre único para la imagen para evitar colisiones
                $imageName = time() . '_' . uniqid() . '.' . $image->extension();

                // Mueve la imagen a la carpeta public/images
                $image->move(public_path('images'), $imageName);

                // Añade el nombre del archivo a la lista de imágenes del mueble
                $imagePaths[] = $imageName;
            }
        }

        // Si el mueble no tenía imagen principal ('main_image'), asigna la primera que se subió.
        // Asumo que tienes una columna 'main_image' en tu tabla.
        if (!$mueble->main_image && !empty($imagePaths)) {
            $mueble->main_image = $imagePaths[0];
        }

        $mueble->images = $imagePaths;
        $mueble->save(); // Guardamos los cambios en la base de datos.

        return back()->with('success', 'Imágenes subidas correctamente.');
    }
}
