<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Http\Request;

class PreferenciasController extends Controller
{
    /**
     * Muestra la vista de preferencias.
     * El sesionId nos lo pasa la plantilla 'app.blade.php' en la URL.
     */
    public function show(Request $request)
    {
        $sesionId = $request->query('sesionId');

        // Pasamos el sesionId a la vista para que pueda incluirlo en el formulario
        return view('preferencias', ['sesionId' => $sesionId]);
    }

    /**
     * Actualiza las preferencias del usuario.
     * Esta es la lógica que sigue el requisito 2.e
     */
    public function update(Request $request)
    {
        // Validamos los datos del formulario
        $data = $request->validate([
            'tema' => ['required', 'string', 'in:claro,oscuro,sistema'],
            'moneda' => ['required', 'string', 'in:USD,EUR,GBP'],
            'tamaño' => ['required', 'integer', 'min:6', 'max:24'],
            'sesionId' => ['required', 'string'], // El sesionId debe venir del campo oculto
        ]);

        $sesionId = $data['sesionId'];
        $cookieName = null;

        // 1. Buscamos al usuario en el array de la sesión
        $allUsers = Session::get('usuarios', []);
        if (isset($allUsers[$sesionId])) {
            $activeUser = json_decode($allUsers[$sesionId]);
            // 2. Obtenemos el nombre de la cookie que le corresponde
            $cookieName = $activeUser->cookie_name ?? null;
        }

        if (!$cookieName || !$request->hasCookie($cookieName)) {
            // Si no encontramos al usuario/cookie, volvemos con error
            return redirect()->route('principal', ['sesionId' => $sesionId])
                             ->withErrors('No se pudo encontrar la cookie para guardar las preferencias.');
        $usuario = User::verifyUser($datos['email'], $datos['password']);
        if (!$usuario) {
            // Vuelve hacia atrás en el navegador y envia un objeto messageBag propio de Laravel
            // con una array de errores.
            return back()->withErrors(['errorCredenciales' => 'Credenciales no válidas']);
        }

        // 3. Leemos la cookie actual para preservar otros datos (email, sesionId original, etc.)
        $cookieData = json_decode($request->cookie($cookieName), true);

        // 4. Actualizamos solo los valores de preferencias
        $cookieData['tema'] = $data['tema'];
        $cookieData['moneda'] = $data['moneda'];
        $cookieData['tamaño'] = $data['tamaño']; // Guardamos como número

        $cookieDuration = config('session.lifetime', 120);

        // 5. Creamos la nueva cookie actualizada
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

        // 6. Redirigimos a principal (pasando el sesionId) y adjuntamos la cookie
        return redirect()
            ->route('principal', ['sesionId' => $sesionId])
            ->withCookie($cookie);
    }
}

