<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;

class User
{

    private $id;
    private $email;
    private $password;
    private $name;
    private $rol;


    public function __construct($id, $email, $password, $name, $rol)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->rol = $rol;
    }

    private static function userData()
    {
        // Creamos varios usuarios para iniciar sesión en la aplicación
        return [
            new User(1, 'admin@correo.com', '1234', 'Admin', 'admin'),
            new User(2, 'jose@correo.com', '1234', 'Jose', 'user'),
            new User(3, 'user2@correo.com', '1234', '', 'user'),
        ];
    }

    public static function verifyUser($email, $password):User|null {
        foreach (User::userData() as $user) {
            if ($user->email === $email && $user->password === $password) {
                return $user;
            }
        }
        return null;
    }

    public static function activeUserSesion($sesionId)
    {
        if ($sesionId != null) {
            // listado de uuarios activos
            $activeUsersList = Session::get('usuarios');

            // user activo
            if ($activeUsersList) {
                return $activeUsersList[$sesionId] ? json_decode($activeUsersList[$sesionId]) : null;
            }
        }
        return null;
    }


    /*

    */

}
