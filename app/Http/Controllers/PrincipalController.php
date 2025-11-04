<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Furniture;
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
    private $sessionKey = 'muebles_crud_session';

    private function getMuebles()
    {
        // Intenta obtener los muebles de la sesión
        $muebles = Session::get($this->sessionKey);

        // Si la sesión tiene una colección de muebles, la devuelve
        if ($muebles instanceof \Illuminate\Support\Collection) {
            return $muebles;
        }

        // Si no, carga los datos mock
        $muebles = collect(Furniture::getMockData());

        // Guarda los muebles en la sesión para futuros usos
        Session::put($this->sessionKey, $muebles);

        return $muebles;
    }

    public function index(Request $request)
    {

        $activeSesionId = $request->query('sesionId');


        $activeUser = null;
        $cookieName = null;
        $preferencias = [ // Valores por defecto
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 4,
        ];

        if ($activeSesionId && Session::has('usuarios')) {
            $usuarios = Session::get('usuarios');
            if (isset($usuarios[$activeSesionId])) {
                $activeUser = json_decode($usuarios[$activeSesionId]);
                $cookieName = 'preferencias_' . $activeUser->id;
            }
        }

        // Leemos la cookie usando el objeto Request
        if ($cookieName && $request->hasCookie($cookieName)) {
            $preferencias = array_merge($preferencias, json_decode($request->cookie($cookieName), true));
        }

        // Obtenemos los datos
        $categories = Category::getMockData();
        $featured = $this->getMuebles()->filter(fn($featured) => $featured->isSalient())->take(6)->values();


        // La vista 'principal' ahora recibirá $categories, $featured, $activeSesionId y $preferencias
        return view('principal', compact('categories', 'featured', 'activeSesionId', 'preferencias'));
    }
}
