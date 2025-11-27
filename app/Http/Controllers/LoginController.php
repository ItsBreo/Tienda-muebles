<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\User;
use Carbon\Carbon; // Necesario para manejar fechas y horas

class LoginController extends Controller
{
    /**
     * Muestra la vista de login.
     */
    public function show()
    {
        return view('login');
    }

    /**
     * Procesa el intento de login.
     */
    public function login(Request $request)
    {
        // 1. Validar los datos del formulario
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Buscar el usuario en la Base de Datos
        // (No usamos Auth::attempt porque necesitamos controlar el bloqueo manualmente)
        $user = User::where('email', $credentials['email'])->first();

        // Si no existe el usuario, devolvemos error genérico por seguridad
        if (!$user) {
            return back()->withErrors(['autenticationError' => 'Credenciales incorrectas.']);
        }

        // 3. Verificar si la cuenta está BLOQUEADA
        // Si tiene fecha de bloqueo y esa fecha es futura...
        if ($user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
            $minutosRestantes = Carbon::now()->diffInMinutes($user->locked_until) + 1;
            return back()->withErrors([
                'autenticationError' => "Cuenta bloqueada temporalmente. Inténtalo de nuevo en $minutosRestantes minutos."
            ]);
        }

        // 4. Verificar la Contraseña
        if (!Hash::check($credentials['password'], $user->password)) {
            // ¡Contraseña incorrecta!

            // Incrementamos el contador de fallos en la BD
            $user->increment('failed_attempts');

            // Si llega a 3 fallos, bloqueamos la cuenta
            if ($user->failed_attempts >= 3) {
                $user->update([
                    'locked_until' => Carbon::now()->addMinutes(5), // Bloqueo de 5 minutos
                    'failed_attempts' => 0 // Opcional: resetear contador tras bloquear
                ]);

                return back()->withErrors([
                    'autenticationError' => 'Has excedido el número de intentos. Tu cuenta ha sido bloqueada por 5 minutos.'
                ]);
            }

            // Si no ha llegado al límite, mostramos error y los intentos restantes
            $intentosRestantes = 3 - $user->failed_attempts;
            return back()->withErrors([
                'autenticationError' => "Contraseña incorrecta. Te quedan $intentosRestantes intentos."
            ]);
        }

        // 5. ¡LOGIN EXITOSO!

        // Reseteamos los contadores de seguridad
        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null
        ]);

        // ---------------------------------------------------------
        // LÓGICA DE SESIÓN MANUAL (Requisito 2.5: Multi-pestaña)
        // ---------------------------------------------------------

        // Generamos un ID único para esta pestaña/sesión
        $sesionId = session()->getId() . "_" . $user->id . "_" . time();

        // Datos mínimos para guardar en la sesión (sin password)
        $userDataSesion = [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            // Asumimos que tienes la relación 'role' definida en el modelo User
            'rol_name' => $user->role ? $user->role->name : 'Cliente',
            'sesion_id' => $sesionId,
        ];

        // Guardamos en el array global de 'usuarios' dentro de la sesión de Laravel
        $users = Session::get('usuarios', []);
        $users[$sesionId] = json_encode($userDataSesion);
        Session::put('usuarios', $users);
        Session::save(); // Forzamos el guardado

        // ---------------------------------------------------------
        // GESTIÓN DE PREFERENCIAS (Cookie)
        // ---------------------------------------------------------
        $cookieName = 'preferencias_' . $user->id;

        // Si el usuario no tiene cookie de preferencias, creamos una por defecto
        if ($request->cookie($cookieName) == null) {
            $defaultPreferences = [
                'tema' => 'claro',
                'moneda' => 'EUR',
                'tamaño' => 6, // 6/12/24 según PDF
            ];

            $cookie = Cookie::make(
                $cookieName,
                json_encode($defaultPreferences),
                60 * 24 * 30 // 30 días
            );

            // Redirigimos adjuntando la cookie
            return redirect()->route('principal', ['sesionId' => $sesionId])->withCookie($cookie);
        }

        // Redirigimos normalmente
        return redirect()->route('principal', ['sesionId' => $sesionId]);
    }

    /**
     * Cierra la sesión de la pestaña actual.
     */
    public function logout(Request $request)
    {
        $sesionId = $request->input('sesionId');

        // Solo eliminamos la sesión de ESTA pestaña del array
        if ($sesionId) {
            $users = Session::get('usuarios', []);
            if (isset($users[$sesionId])) {
                unset($users[$sesionId]);
                Session::put('usuarios', $users);
                Session::save();
            }
        }

        // Redirigimos al login
        return redirect()->route('login.show');
    }
}
