<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class PreferenciasController extends Controller
{

    /**
     * Muestra la vista del formulario de preferencias.
     * Esta ruta es llamada por LoginController la primera vez que
     * un usuario inicia sesión y no tiene la cookie.
     */
    public function show()
    {
        // Simplemente mostramos la vista que creamos
        return view('preferencias');
    }

    /**
     * Almacena las preferencias seleccionadas en el formulario.
     * Esta ruta es llamada por el 'action' del formulario.
     */
    public function update(Request $request)
    {
        // 1. Validar los datos que vienen del formulario
        $validatedData = $request->validate([
            'tema' => 'required|string|max:50',
            'moneda' => 'required|string|max:10',
            'tamaño' => 'required|integer|min:2|max:6',
        ]);

        // 2. Obtener el nombre de la cookie que guardamos en la sesión
        // durante el login.
        $id_COOKIE = Session::get('current_cookie_name');

        // 3. Seguridad: Si no hay nombre de cookie en la sesión,
        // significa que la sesión se perdió. Redirigimos a login.
        if (!$id_COOKIE) {
            return redirect()->route('login.show')->withErrors([
                'autenticationError' => 'Tu sesión ha expirado. Por favor, inicia sesión de nuevo.'
            ]);
        }

        // 4. Leer los datos de la cookie actual que creó LoginController
        $cookieJson = $request->cookie($id_COOKIE);

        // Decodificamos el JSON a un array de PHP
        // Si la cookie no existiera (raro), usamos un array vacío
        $cookieData = $cookieJson ? json_decode($cookieJson, true) : [];

        // 5. Actualizar el array con los nuevos datos del formulario.
        // Esto conservará los datos antiguos (como 'sesionId' y 'email')
        // y rellenará los que estaban vacíos ('tema', 'moneda', 'tamaño').
        $cookieData['tema'] = $validatedData['tema'];
        $cookieData['moneda'] = $validatedData['moneda'];
        $cookieData['tamaño'] = $validatedData['tamaño'];

        // 6. Obtener la duración de la cookie de la configuración
        $cookieDuration = config('session.lifetime', 120);

        // 7. Crear una NUEVA cookie (actualizada)
        $newCookie = Cookie::make(
            $id_COOKIE,                     // El mismo nombre de la cookie
            json_encode($cookieData),       // El array actualizado, codificado a JSON
            $cookieDuration,                // Duración
            '/',                             // Path
            null,                            // Domain
            config('session.secure', false), // Secure
            true,                            // httpOnly
            false,
            config('session.same_site', 'lax') // SameSite
        );

        // 8. Redirigir al usuario a la página principal
        // y adjuntar la cookie actualizada a esa respuesta.
        return redirect()->route('principal')->withCookie($newCookie);
    }
}
