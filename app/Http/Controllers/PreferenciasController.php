<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class PreferenciasController extends Controller
{


    public function show()
    {

        return view('preferencias');
    }


    public function update(Request $request)
    {

        $validatedData = $request->validate([
            'tema' => 'required|string|max:50',
            'moneda' => 'required|string|max:10',
            'tamaño' => 'required|integer|min:2|max:6',
        ]);

        // Obtener el nombre de la cookie que guardamos en la sesión

        $id_COOKIE = Session::get('current_cookie_name');

        // Si no hay nombre de cookie en la sesión,
        // significa que la sesión se perdió. Redirigimos a login.
        if (!$id_COOKIE) {
            return redirect()->route('login.show')->withErrors([
                'autenticationError' => 'Tu sesión ha expirado. Por favor, inicia sesión de nuevo.'
            ]);
        }


        $cookieJson = $request->cookie($id_COOKIE);

        // Si la cookie no existiera, usamos un array vacío
        $cookieData = $cookieJson ? json_decode($cookieJson, true) : [];

        // Actualizar el array con los nuevos datos del formulario
        // y rellenando los que estaban vacíos ('tema', 'moneda', 'tamaño').
        $cookieData['tema'] = $validatedData['tema'];
        $cookieData['moneda'] = $validatedData['moneda'];
        $cookieData['tamaño'] = $validatedData['tamaño'];

        $cookieDuration = config('session.lifetime', 120);

        // 7. Crear una NUEVA cookie (actualizada)
        $newCookie = Cookie::make(
            $id_COOKIE,
            json_encode($cookieData),
            $cookieDuration,
            '/',
            null,
            config('session.secure', false),
            true,
            false,
            config('session.same_site', 'lax')
        );

        // Redirigir al usuario a la página principal
        // y adjuntar la cookie actualizada
        return redirect()->route('principal')->withCookie($newCookie);
    }
}
