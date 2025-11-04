<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Furniture;
use App\Models\User;
use Illuminate\Support\Facades\Session;

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

    public function show(Request $request, $id)
    {
        // Carga la sesión y las preferencias
        $sesionData = $this->getSesionYPreferencias($request);

        // Busca el mueble
        $muebles = $this->getMuebles();
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404, 'Mueble no encontrado');
        }

        return view('muebles.show', array_merge($sesionData, [
            'mueble' => $mueble,
        ]));
    }
}
