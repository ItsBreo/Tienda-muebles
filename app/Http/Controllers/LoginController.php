<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{


    public function show()
    {
        return view('login');
    }


    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4'],
        ]);


        $user = User::verifyUser($credentials['email'], $credentials['password']);


        if ($user instanceof User) {


            $request->session()->regenerate();

            // nombre de la cookie
            $id_COOKIE = 'preferencias_' . $user->getId();


            $sesionId = $request->session()->getId() . "_" . $user->getId();


            $users = Session::get('usuarios', []);


            $userDataSesion = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'rol' => $user->getRol(),
                'fecha_ingreso' => date('Y-m-d H:i:s'),
                'sesion_id' => $sesionId
            ];

            $users[$sesionId] = json_encode($userDataSesion);
            Session::put('usuarios', $users);

            //Guardamos el sesionId y el nombre de la cookie en la sesión
            Session::put('current_sesion_id', $sesionId);
            Session::put('current_cookie_name', $id_COOKIE);



            if (!$request->cookie($id_COOKIE)) {
                // El usuario no tiene la cookie, la creamos

                $cookieData = [
                    'sesionId' => $sesionId,
                    'email' => $user->getEmail(),
                    'tema' => '',
                    'moneda' => '',
                    'tamaño' => ''
                ];

                $cookieDuration = config('session.lifetime', 120);

                $cookie = Cookie::make(
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

                // Redirigimos a la página de preferencias CON la cookie
                return redirect()->route('preferencias.show')->withCookie($cookie);
            }

            // Si el usuario ya tenía la cookie, lo mandamos al principal
            return redirect()->route('principal');
        }

        // 10. Si la autenticación falla
        return back()->withErrors([
            'autenticationError' => 'Las credenciales no son correctas',
        ])->withInput($request->only('email'));
    }


    public function logout(Request $request)
    {
        // Obtener los datos de la sesión
        $sesionId = Session::get('current_sesion_id');
        $id_COOKIE = Session::get('current_cookie_name');

        // 2. Limpiar el array de usuarios en la sesión (como en tu código original)
        if ($sesionId) {
            $users = Session::get('usuarios', []); // Usar 'usuarios', no 'users'
            if (isset($users[$sesionId])) {
                unset($users[$sesionId]);
                Session::put('usuarios', $users);
            }
        }

        // 3. Invalidar la sesión actual
        // Esto borra 'current_sesion_id', 'current_cookie_name' y 'usuarios'
        $request->session()->invalidate();

        // 4. Regenerar el token CSRF
        $request->session()->regenerateToken();

        // 5. Crear una cookie "olvidada" (expirada)
        // Usamos el nombre de la cookie que guardamos en la sesión
        $cookie = Cookie::forget($id_COOKIE);

        // 6. Redirigir al principal y adjuntar la cookie expirada
        return redirect()->route('principal')->withCookie($cookie);
    }
}

