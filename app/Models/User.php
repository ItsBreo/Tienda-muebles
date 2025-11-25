<?php

namespace App\Models;

// 1. Importar las clases necesarias de Eloquent y para autenticación.
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Importamos Authenticatable
use Illuminate\Support\Facades\Session;

// 2. Cambiamos la clase para que herede de Authenticatable.
class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'password',
        'role_id', // Cambiamos 'rol' por 'role_id'
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define la relación con el modelo Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Comprueba si el usuario tiene un rol específico.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        // Usamos la relación para comprobar el nombre del rol.
        return $this->role && $this->role->name === $roleName;
    }
}
