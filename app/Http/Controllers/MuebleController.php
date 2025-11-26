<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Furniture;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class MuebleController extends Controller
{
    /**
     * Función helper privada para cargar la sesión y las preferencias.
     */
    private function getSesionYPreferencias(Request $request)
    {
        $defaultPrefs = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        if (auth()->check()) {
            $cookieName = 'preferencias_' . auth()->user()->id;
            $cookieData = json_decode($request->cookie($cookieName), true);
            return $cookieData ? array_merge($defaultPrefs, $cookieData) : $defaultPrefs;
        }

        return $defaultPrefs;
    }

    public function show(Request $request, $id)
    {
        // Carga la sesión y las preferencias
        $preferencias = $this->getSesionYPreferencias($request);

        // Busca el mueble
        $mueble = Furniture::find((int)$id);

        if (!$mueble) {
            abort(404, 'Mueble no encontrado');
        }

        // Crear cookie para el mueble mostrado (mueble_{id}) por 30 días
        Cookie::queue("mueble_{$mueble->id}", json_encode($mueble), 60 * 24 * 30);

        return view('muebles.show', [
            'mueble' => $mueble,
            'preferencias' => $preferencias,
        ]);
    }
}
