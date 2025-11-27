<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Furniture;
use App\Models\User;
use Illuminate\Support\Facades\Session; // <-- 1. AÑADIMOS EL IMPORT DE SESSION

// Controlador Principal
class PrincipalController extends Controller
{
    /**
     * Muestra la página principal.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        $preferencias = [ // Valores por defecto
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 4,
        ];

        // Si hay un usuario para esta sesión, leemos su cookie de preferencias.
        if ($user) {
            $cookieName = 'preferencias_' . $user->id;

            if ($request->hasCookie($cookieName)) {
                $preferencias = array_merge($preferencias, json_decode($request->cookie($cookieName), true));
            }
        }

        // Obtenemos los datos
        $categories = Category::all();
        $featured = Furniture::where('is_salient', true)->take(6)->get();

        // Pasamos todas las variables necesarias a la vista, incluyendo el sesionId
        return view('principal', compact('categories', 'featured', 'preferencias', 'user', 'sesionId'));
    }
}
