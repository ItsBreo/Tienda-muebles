<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\SessionLog;
use App\Models\User;
use Carbon\Carbon;

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

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);


        $user = User::where('email', $credentials['email'])->first();

        // Si no existe el usuario, devolvemos error
        if (!$user) {
            return back()->withErrors(['autenticationError' => 'Credenciales incorrectas.']);
        }

        // Verificar si la cuenta está BLOQUEADA
        if ($user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
            $minutosRestantes = Carbon::now()->diffInMinutes($user->locked_until) + 1;
            return back()->withErrors([
                'autenticationError' => "Cuenta bloqueada temporalmente. Inténtalo de nuevo en $minutosRestantes minutos."
            ]);
        }

        // Verificar la Contraseña
        if (!Hash::check($credentials['password'], $user->password)) {

            // Incrementamos el contador de fallos en la BD
            $user->increment('failed_attempts');

            // Si llega a 3 fallos, bloqueamos la cuenta 5 min
            if ($user->failed_attempts >= 3) {
                $user->update([
                    'locked_until' => Carbon::now()->addMinutes(5),
                    'failed_attempts' => 0
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

        // Reseteamos los contadores de seguridad si la autenticación es exitosa
        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => Carbon::now(),
        ]);

        $request->session()->regenerate();

        // Generamos un ID único para esta pestaña/sesión
        $sesionId = session()->getId() . "_" . $user->id . "_" . time();

        // Creamos el registro en logs.
        SessionLog::create([
            'session_id' => $sesionId,
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => Carbon::now(),
        ]);

        // Guardamos los datos del usuario en la sesión
        $userDataSesion = [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'rol_name' => $user->role ? $user->role->name : 'Cliente',
            'sesion_id' => $sesionId,
        ];

        // Guardamos en el array global de 'usuarios' dentro de la sesión de Laravel
        $users = Session::get('usuarios', []);
        $users[$sesionId] = json_encode($userDataSesion);
        Session::put('usuarios', $users);
        Session::save(); // Forzamos el guardado

        Cookie::queue('current_sesionId', $sesionId, 60 * 24 * 30); // 30 días

        $cookieName = 'preferencias_' . $user->id;

        // Si el usuario no tiene cookie de preferencias, creamos una por defecto
        if ($request->cookie($cookieName) == null) {
            $defaultPreferences = [
                'tema' => 'claro',
                'moneda' => 'EUR',
                'tamaño' => 6,
            ];

            $cookie = Cookie::make(
                $cookieName,
                json_encode($defaultPreferences),
                60 * 24 * 30
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
        $cookieToForget = null;
        $users = Session::get('usuarios', []);

        // Si no hay sesionId (caso del admin), buscamos la primera sesión activa
        if (!$sesionId && !empty($users)) {
            // array_key_first() obtiene la clave del primer elemento del array 'usuarios'
            // el admin siempre será la ID 1, por lo que es suficiente
            $sesionId = array_key_first($users);
        }

        if ($sesionId) {
            if (empty($users)) {
                $users = Session::get('usuarios', []);
            }

            // Verificamos si existe la sesión para obtener el ID del usuario
            if (isset($users[$sesionId])) {
                $userData = json_decode($users[$sesionId]);

                if (isset($userData->id)) {
                    $cookieName = 'preferencias_' . $userData->id;
                    $cookieToForget = Cookie::forget($cookieName);
                }

                // Buscamos el log de sesión correspondiente y lo actualizamos.
                $log = SessionLog::where('session_id', $sesionId)->whereNull('logout_at')->first();
                if ($log) {
                    $log->update([
                        'logout_at' => Carbon::now()
                    ]);
                }

                // Eliminamos al usuario del array de sesión
                unset($users[$sesionId]);
                Session::put('usuarios', $users);
                Session::save();
            }
        }

        // Redirigimos adjuntando la orden de olvidar la cookie
        if ($cookieToForget) {
            return redirect()->route('principal')->withCookie($cookieToForget);
        }

        return redirect()->route('principal');
    }
}
