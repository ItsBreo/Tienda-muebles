<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;

class ProductosGaleriaController extends Controller
{
    private $cookieName = 'muebles_crud';
    private $cookieMinutes = 60 * 24 * 7; // 1 semana

    /**
     * Obtiene los muebles desde la cookie.
     * Replicado de AdministracionController para mantener la consistencia.
     */
    private function getMuebles()
    {
        $mueblesJson = Cookie::get($this->cookieName);
        if ($mueblesJson) {
            return collect(json_decode($mueblesJson, true))->map(function ($item) {
                return new Furniture(
                    $item['id'],
                    $item['category_id'],
                    $item['name'],
                    $item['description'],
                    $item['price'],
                    $item['stock'],
                    $item['materials'] ?? '',
                    $item['dimensions'] ?? '',
                    $item['main_color'],
                    $item['is_salient'],
                    $item['images']
                );
            });
        }
        // Si no hay cookie, devuelve una colección vacía para evitar errores.
        return collect([]);
    }

    /**
     * Guarda la colección de muebles en la cookie.
     * Replicado de AdministracionController.
     */
    private function saveMuebles($muebles)
    {
        Cookie::queue($this->cookieName, $muebles->toJson(), $this->cookieMinutes);
    }

    /**
     * Sube y guarda las imágenes para un mueble específico.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $muebles = $this->getMuebles();
        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) {
            return back()->with('error', 'Mueble no encontrado.');
        }

        $mueble = $muebles[$muebleIndex];
        $imagePaths = $mueble->getImages();

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

        // Si el mueble no tenía imagen principal, asigna la primera que se subió
        if ($mueble->getMainImage() === 'default.jpg' && !empty($imagePaths)) {
            // Filtra para asegurarse de que no se asigne 'default.jpg' si aún está en la lista
            $firstImage = collect($imagePaths)->first(fn($img) => $img !== 'default.jpg');
            if ($firstImage) {
                $mueble->setImages($firstImage);
            }
        }

        $mueble->setImages($imagePaths);
        $muebles[$muebleIndex] = $mueble;
        $this->saveMuebles($muebles);

        return back()->with('success', 'Imágenes subidas correctamente.');
    }
}
