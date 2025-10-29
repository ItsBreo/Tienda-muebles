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

    // Función para verificar que el usuario es admin
    public function isAdmin(): bool{
        if ($this->role === 'admin') {
            return true;
        }
        else {
            return false;
        }
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


    // Getters

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRol() {
        return $this->rol;
    }

    public function getPassword() {
        return $this->password;
    }


    // Setters

    public function setName($name) {
        $this->name = $name;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setRol($rol) {
        $this->rol = $rol;
    }

    public function setId($id) {
        $this->id = $id;
    }


    /*

    */

}
