<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
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
            // Autenticación correcta
            RateLimiter::clear($throttleKey); // Limpiamos los intentos fallidos.

            $user = Auth::user();
            Auth::logout(); // Cerramos la sesión de Laravel para no usar su cookie.

            // Generamos un ID de sesión único para esta pestaña
            $sesionId = uniqid('sesion_', true);

            // Guardamos el usuario (serializado) en la sesión del servidor con la clave única.
            // Usamos serialize para guardar el objeto completo.
            Session::put($sesionId, serialize($user));

            // Creamos la ruta de redirección con el sesionId
            if ($user->hasRole('Admin')) {
                $redirectRoute = route('admin.muebles.index', ['sesionId' => $sesionId]);
            } else {
                $redirectRoute = route('principal', ['sesionId' => $sesionId]);
            }

            // Redirigimos a la ruta correspondiente.
            return redirect($redirectRoute);
        }

        // --- El resto del código para intentos fallidos permanece igual ---

        // 6. Si el login falla, incrementamos el contador de intentos.
        RateLimiter::hit($throttleKey, 300); // Bloqueo de 300 segundos (5 minutos)

        // 7. Devolvemos al usuario a la página de login con un error.
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        // Olvidamos la sesión de la pestaña actual.
        if ($request->has('sesionId')) {
            Session::forget($request->input('sesionId'));
        }

        // Ya no necesitamos invalidar la sesión global de Laravel.
        // Simplemente redirigimos a la página de inicio.
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
