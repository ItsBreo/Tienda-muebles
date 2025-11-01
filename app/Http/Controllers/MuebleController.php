<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Furniture;
use App\Models\User;

class MuebleController extends Controller
{
    /**
     * Función helper privada para cargar la sesión y las preferencias.
     */
    private function getSesionYPreferencias(Request $request)
    {
        $activeSesionId = $request->query('sesionId');
        $activeUser = null;

        $defaultPrefs = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        if ($activeSesionId) {
            $activeUser = User::activeUserSesion($activeSesionId);
        }

        if ($activeUser) {
            $cookieName = 'preferencias_' . $activeUser->id;
            $cookieData = json_decode($request->cookie($cookieName), true);
            $preferencias = $cookieData ? array_merge($defaultPrefs, $cookieData) : $defaultPrefs;
        } else {
            $preferencias = $defaultPrefs;
        }

        return compact('activeSesionId', 'activeUser', 'preferencias');
    }



    public function show(Request $request, $id)
    {
        // Carga la sesión y las preferencias
        $sesionData = $this->getSesionYPreferencias($request);

        // Busca el mueble
        $mueble = Furniture::findById((int)$id);

        if (!$mueble) {
            abort(404, 'Mueble no encontrado');
        }

        return view('muebles.show', array_merge($sesionData, [
            'mueble' => $mueble,
        ]));
    }
}
