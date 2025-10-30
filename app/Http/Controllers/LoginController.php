<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class LoginController extends Controller
{

    private $id_COOKIE = 'preferencias' . user.getId();

    public function show() {
        return view('login');
    }


public function login(Request $request) {
     $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:4'],
    ]);



    $user = User::verifyUser($data['email'], $data['password']);


    if (!$user) {
        return back() -> withErrors(['autenticationError' => 'Las credenciales no son correctas']);
    }

    $sesionId = Session::getId() . "_" . $user->getId();

    $users = Session::get('usuarios', []);

    $currentUser = null;

    $currentUser = isset($users[$sesionId]) == true? json_decode($users[$sesionId]) : null;

    if($currentUser == null) {

        $userDataSesion = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'rol' => $user->getRol(),
            'fecha_ingreso' => date('Y-m-d H:i:s'),
            'sesion_id' => $sesionId
        ];

        $userJson = json_encode($userDataSesion);

        $users[$sesionId] = $userJson;

        Session::put('usuarios', $users);


    if (request()->cookie($this->id_COOKIE) == null) {
            $cookieData = [
            'sesionId' => $sesionId,
            'email' => $user->getEmail(),
            'tema' => '',
            'moneda' => '',
            'tamaño' => ''
        ];

        $cookieDuration = config('session.lifetime', 120);

        Cookie::queue(
            name: $this->id_COOKIE,
            value: json_encode($cookieData),
            minutes: $cookieDuration,
            path: '/',
            domain: null,
            secure: config('session.secure', false),
            httpOnly: true,
            sameSite: config('session.same_site', 'lax')
        );

        return redirect() -> route('preferencias.index', ['sesionId' => $sesionId]);
        }
    }

    // TODO: redireccionar a la pagina principal según se defina la ruta
    // por ahora redirige a "principal" que es nada
    return redirect() -> route('principal.show', ['sesionId' => $sesionId]);
}


    public function logout(Request $request) {

        $sesionId = $request->query('sesionId');
        $users = Session::get('users');

        if(isset($users[$sesionId]) == true) {
            unset($users[$sesionId]);
            Session::put('users', $users);
        }

        //TODO: redireccionar a la pagina principal segun se defina la ruta
        return redirect() -> route('principal');

        if (Cookie::has($this->id_COOKIE)) {
            Cookie::forget($this->id_COOKIE);
        }
    }




}
