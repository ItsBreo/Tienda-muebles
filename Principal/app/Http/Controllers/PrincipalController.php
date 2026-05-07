<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiFurnitureService;
use Illuminate\Support\Facades\Session;

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
        
        // El usuario logueado se guardará en la sesión (lo implementaremos en el login)
        $user = Session::get('user');

        $preferencias = [ // Valores por defecto
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 4,
        ];

        // Si hay un usuario para esta sesión, leemos su cookie de preferencias.
        if ($user) {
            $cookieName = 'preferencias_' . $user['id'];

            if ($request->hasCookie($cookieName)) {
                $preferencias = array_merge($preferencias, json_decode($request->cookie($cookieName), true));
            }
        }

        // Usamos el servicio en lugar de Eloquent
        $apiFurniture = new ApiFurnitureService();
        $categories = $apiFurniture->getCategories();
        $featured = $apiFurniture->getFeatured();

        // Pasamos todas las variables necesarias a la vista, incluyendo el sesionId
        return view('principal', compact('categories', 'featured', 'preferencias', 'user', 'sesionId'));
    }
}
