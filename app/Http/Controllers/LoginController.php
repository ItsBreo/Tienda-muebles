<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class LoginController extends Controller
{
    public function show()
    {
        // Pasamos un sesionId nulo a la vista de login
        return view('login', ['sesionId' => null]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        $user = User::verifyUser($data['email'], $data['password']);

        if (!$user) {
            return back()->withErrors(['autenticationError' => 'Las credenciales no son correctas']);
        }

        // Creamos un ID único para ESTA instancia de login
        $sesionId = Session::getId() . "_" . $user->getId() . "_" . time();
        $id_COOKIE = 'preferencias_' . $user->getId();

        // Obtenemos el array de usuarios de la sesión
        $users = Session::get('usuarios', []);

        // Guardamos los datos
        $userDataSesion = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'rol' => $user->getRol(),
            'fecha_ingreso' => date('Y-m-d H:i:s'),
            'sesion_id' => $sesionId,
            'cookie_name' => $id_COOKIE, // Guardamos el nombre de la cookie a usar
        ];

        // Añadimos este usuario al array de la sesión
        $users[$sesionId] = json_encode($userDataSesion);
        Session::put('usuarios', $users);

        if ($user->getRol() == 'admin') {
            return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId]);
        }

        // Comprobamos si la cookie ya existia, si no, la creamos
        if ($request->cookie($id_COOKIE) == null) {
            $cookieData = [
                'sesionId' => $sesionId,
                'email' => $user->getEmail(),
                'tema' => '',
                'moneda' => '',
                'tamaño' => '',
            ];

            $cookieDuration = config('session.lifetime', 120);

            // Creamos la cookie
            $cookie = Cookie::make(
                name: $id_COOKIE,
                value: json_encode($cookieData),
                minutes: $cookieDuration,
                path: '/',
                domain: null,
                secure: config('session.secure', false),
                httpOnly: true,
                sameSite: config('session.same_site', 'lax')
            );

            // Redirigimos a preferencias (pasando el sesionId) y adjuntamos la cookie
            return redirect()
                ->route('preferencias.show', ['sesionId' => $sesionId])
                ->withCookie($cookie);
        }

        // Si la cookie ya existía, vamos a principal (pasando el sesionId)
        if ($user->getRol() == 'admin') {
            return redirect()->route('admin', ['sesionId' => $sesionId]);
        } else {
        return redirect()->route('principal', ['sesionId' => $sesionId]);
        }
    }

    public function logout(Request $request)
    {
        // El sesionId nos dice que pestaña/usuario cerrar
        $sesionId = $request->input('sesionId');

        $users = Session::get('usuarios', []);
        $cookieToForget = null;

        if (isset($users[$sesionId])) {
            // Obtenemos el nombre de la cookie antes de borrar
            $userData = json_decode($users[$sesionId]);
            $cookieToForget = $userData->cookie_name ?? null;

            // Eliminamos solo a este usuario del array
            unset($users[$sesionId]);
            Session::put('usuarios', $users);
        }

        $response = redirect()->route('principal');

        // Olvidamos la cookie asociada si la encontramos
        if ($cookieToForget) {
            $response->withCookie(Cookie::forget($cookieToForget));
        }

        return $response;
    }
}
