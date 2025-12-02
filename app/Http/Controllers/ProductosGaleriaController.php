<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;

class ProductosGaleriaController extends Controller
{
    /**
     * Middleware manual para proteger rutas de admin.
     */
    private function checkAdmin(Request $request)
    {
        // 1. Obtenemos el sesionId de la petición.
        $sesionId = $request->route('sesionId') ?? $request->query('sesionId') ?? $request->input('sesionId');

        if (!$sesionId) {
            $sesionId = $request->cookie('current_sesionId');
        }

        if (!$sesionId) {
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        $user = User::activeUserSesion($sesionId);

        // 2. Comprobamos si existe un usuario para esa sesión.
        if (! $user) {
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        // 3. Comprobamos si el usuario es admin
        if ($user->isAdmin()) {
            return true;
        }

        // No es admin. Le redirigimos a la página principal con un error.
        return redirect()->route('principal', ['sesionId' => $sesionId])
            ->with('error-admin', 'Acceso denegado. No tienes permisos de administrador.');
    }

    /**
     * Sube múltiples imágenes para una galería de un mueble.
     * POST: productos/{mueble}/galeria
     */
    public function store(Request $request, Furniture $mueble)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        $request->validate([
            'images'   => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Generar nombre único para la imagen
                $imageName = time() . '_' . uniqid() . '.' . $image->extension();

                // Mover archivo al directorio public/images
                $image->move(public_path('images'), $imageName);

                // Crear registro en la BD
                $mueble->images()->create([
                    'image_path' => 'images/' . $imageName,
                    'is_primary' => false,
                    'alt_text' => $mueble->name . ' - Imagen galería',
                ]);
            }
        }

        return back()->with('success', 'Imágenes subidas correctamente.');
    }

    /**
     * Elimina una imagen de la galería.
     * DELETE: productos/{mueble}/galeria/{image}
     */
    public function destroy(Request $request, Furniture $mueble, Image $image)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Verificar que la imagen pertenece al mueble
        if ($image->furniture_id !== $mueble->id) {
            return back()->with('error', 'La imagen no pertenece a este mueble.');
        }

        // Obtener la ruta de la imagen
        $imagePath = public_path($image->image_path);

        // Eliminar archivo físico
        if (File::exists($imagePath)) {
            File::delete($imagePath);
        }

        // Eliminar registro de la BD
        $image->delete();

        return back()->with('success', 'Imagen eliminada correctamente.');
    }

    /**
     * Establece una imagen como principal.
     * POST: productos/{mueble}/galeria/{image}/principal
     */
    public function setMain(Request $request, Furniture $mueble, Image $image)
    {
        if (($check = $this->checkAdmin($request)) !== true) {
            return $check;
        }

        // Verificar que la imagen pertenece al mueble
        if ($image->furniture_id !== $mueble->id) {
            return back()->with('error', 'La imagen no pertenece a este mueble.');
        }

        // Quitar el is_primary de todas las imágenes del mueble
        $mueble->images()->update(['is_primary' => false]);

        // Establecer esta imagen como principal
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Imagen principal establecida correctamente.');
    }
}
