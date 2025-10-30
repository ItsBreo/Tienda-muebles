<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use App\Models\Usuario;

class PreferenciasController extends Controller
{

    private $id_COOKIE = 'autorizacion_login';

    public function show()
    {
        return view('preferencias');
    }

    public function login(Request $request)
    {
        $datos = $request->validate(
            [
                'email'    => ['required', 'email'],
                'password' => ['required', 'string', 'min:4'],
                'recuerdame' => ['nullable', 'boolean'],
            ],
            [
                'email.required' => 'Debes introducir el email del usuario.',
                'email.email'  => 'Formato de email no válido',
                'password.required' => 'Debes introducir el password de usuario.',
                'password.min'  => 'La longitud del password no es adecuada',
            ],
        );

        $usuario = Usuario::verificarUsuario($datos['email'], $datos['password']);
        if (!$usuario) {
            // Vuelve hacia atrás en el navegador y envia un objeto messageBag propio de Laravel
            // con una array de errores.
            return back()->withErrors(['errorCredenciales' => 'Credenciales no válidas']);
        }

        $datosCookie = [
            'email' => $usuario->email,
            'nombre'  => 'yeray',
            'fecha_ingreso'   => now()->toString(),
        ];

        // Recordar el inicio de sesión 30 días o 1 hora.
        $minutos = $request->boolean('recuerdame') ? 43200 : 60;

        // queue: Laravel se encarga de crear y enviar la cookie al navegador
        // a través de las cabeceras.

        Cookie::queue(
            name: $this->id_COOKIE,
            // Guardamos la información del array en formato JSON para más comodidad
            value: json_encode($datosCookie),
            minutes: $minutos,
            path: '/',
            domain: null,
            secure: config('session.secure', false),
            httpOnly: true,
            sameSite: config('session.same_site', 'lax')
        );

        // Redireccionar rutas
        return redirect()->route('dashboard');
    }

    public function cerrarSesion()
    {
        // forget: modifica el objeto cookie poniendole una fecha expirada
        // queue: lo envia al navegador, sin tenerlo que pasar manualmente.
        Cookie::queue(Cookie::forget($this->id_COOKIE));
        // Pasar estados con redirecciones.
        return redirect()->route('login')->with('estado', 'Sesión cerrada');
    }

    public function index()
    {
        $json = Cookie::get('autorizacion_login');

        if (!$json) {
            return redirect()->route('login');
        }

        $usuario = json_decode($json, true);
        if (!is_array($usuario) || empty($usuario['email'])) {
            return redirect()->route('login');
        }

        return view('dashboard', compact('usuario'));
    }
}
