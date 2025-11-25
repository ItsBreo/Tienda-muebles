<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        // Simplemente muestra la vista de login.
        return view('login');
    }

    public function login(Request $request)
    {
        // 1. Validamos los datos del formulario.
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4'],
        ]);

        // --- Lógica de bloqueo de intentos (Throttling) manual ---

        // 2. Creamos una "llave" única para identificar este intento de login.
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // 3. Comprobamos si se han superado los intentos.
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) { // 3 intentos máximos
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        // 4. Intentamos autenticar al usuario.
        // El método Auth::attempt se encarga de verificar el email y la contraseña (hasheada).
        if (Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            // Si el login es exitoso:
            $request->session()->regenerate(); // Regeneramos la sesión por seguridad.

            RateLimiter::clear($throttleKey); // Limpiamos los intentos fallidos.

            $user = Auth::user();

            // --- Lógica de Cookie de Preferencias ---
            $cookieName = 'preferencias_' . $user->id;
            $cookieRedirect = null;

            // 2. Verificamos si la cookie de preferencias NO existe en la petición.
            if (!$request->hasCookie($cookieName)) {
                // 3. Si no existe, la creamos con valores por defecto.
                $defaultPreferences = [
                    'tema' => 'claro',
                    'moneda' => 'EUR',
                    'tamaño' => 6, // Un valor inicial razonable para la paginación
                ];

                $cookie = Cookie::make(
                    $cookieName,
                    json_encode($defaultPreferences),
                    config('session.lifetime', 120) // Duración de la cookie
                );

                // 4. Preparamos una redirección a la página de preferencias adjuntando la nueva cookie.
                $cookieRedirect = redirect()->route('preferencias.show')->withCookie($cookie);
            }

            // 5. Redirigimos según el rol.
            if ($user->hasRole('Admin')) {
                // Si es admin, siempre va a su panel. La cookie se enviará si es necesario.
                $redirect = redirect()->intended(route('admin.muebles.index'));
                return $cookieRedirect ? $redirect->withCookie($cookieRedirect->headers->getCookies()[0]) : $redirect;
            }

            // 6. Si se debe redirigir a preferencias, lo hacemos. Si no, a la página principal.
            return $cookieRedirect ?? redirect()->intended(route('principal'));
        }

        // 6. Si el login falla, incrementamos el contador de intentos.
        RateLimiter::hit($throttleKey, 300); // Bloqueo de 300 segundos (5 minutos)

        // 7. Devolvemos al usuario a la página de login con un error.
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout(); // Cierra la sesión del usuario.

        $request->session()->invalidate(); // Invalida la sesión.
        $request->session()->regenerateToken(); // Regenera el token CSRF.
        $request->cookie() ?->forget('preferencias_' . Auth::id());

        return redirect('/'); // Redirige a la página de inicio.
    }

    public function register()
    {
        // Muestra la vista de registro.
        return view('registro');
    }

    public function registerUser(Request $request)
    {
        // Este método ya no es necesario aquí. La lógica de creación de usuarios
        // la hemos movido al UserController
        return redirect()->route('login.show')->with('info', 'La funcionalidad de registro ha sido movida.');
    }
}
