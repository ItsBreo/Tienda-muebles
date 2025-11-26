<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class PreferenciasController extends Controller
{
    /**
     * Muestra la vista de preferencias.
     * El sesionId nos lo pasa la plantilla 'app.blade.php' en la URL.
     */
    public function show(Request $request)
    {
        // Necesitamos el sesionId para mantener la navegación
        $sesionId = $request->query('sesionId');
        if (!User::activeUserSesion($sesionId)) {
            return redirect()->route('login.show')->withErrors('Debes iniciar sesión para ver tus preferencias.');
        }


        return view('preferencias' , compact('sesionId'));
    }

    /**
     * Actualiza las preferencias del usuario.
     * Esta es la lógica que sigue el requisito 2.e
     */
    public function update(Request $request)
    {

        // Necesitamos el sesionId para mantener la navegación
        $sesionId = $request->query('sesionId');

        $user = User::activeUserSesion($sesionId);

        // Validamos los datos del formulario
        $data = $request->validate([
            'tema' => ['required', 'string', 'in:claro,oscuro,sistema'],
            'moneda' => ['required', 'string', 'in:USD,EUR,GBP'],
            'tamaño' => ['required', 'integer', 'min:6', 'max:24'],
        ]);


        if (!$user) {
            return redirect()->route('login.show')->withErrors('Debes iniciar sesión para cambiar tus preferencias.');
        }


        $cookieName = 'preferencias_' . $user->id;

        // 3. Leemos la cookie actual para no perder otros datos que pudiera tener
        $cookieData = json_decode($request->cookie($cookieName), true) ?? [];



        // Actualizamos solo los valores de preferencias
        $cookieData['tema'] = $data['tema'];
        $cookieData['moneda'] = $data['moneda'];
        $cookieData['tamaño'] = $data['tamaño']; // Guardamos como número

        $cookieDuration = config('session.lifetime', 120);

        // Creamos la nueva cookie actualizada
        $cookie = Cookie::make(
            name: $cookieName,
            value: json_encode($cookieData),
            minutes: $cookieDuration,
            path: '/',
            domain: null,
            secure: config('session.secure', false),
            httpOnly: true,
            sameSite: config('session.same_site', 'lax')
        );


        // Redirigimos a principal (pasando el sesionId) y adjuntamos la cookie
        return redirect()->route('principal', ['sesionId' => $sesionId])
                         ->with('success', 'Preferencias actualizadas correctamente.')
                         ->withCookie($cookie);
    }
}
