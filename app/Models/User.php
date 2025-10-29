<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;

class User extends Authenticatable
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
            new User(2, 'user@correo.com', '1234', 'User', 'user'),
            new User(3, 'user2@correo.com', '1234', 'User 2', 'user'),
        ];
    }
}
