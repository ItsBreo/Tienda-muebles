<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // ¡Importante para Auth!
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'role_id',
        'last_login_at',
        'failed_attempts',
        'locked_until',
    ];

    /**
     * Los atributos ocultos.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
    ];


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Relación con el carrito de compras.
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Relación con los registros de sesión.
     */
    public function sessionLogs()
    {
        return $this->hasMany(SessionLog::class);
    }


    /**
     * Comprueba si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        // Comparamos directamente la columna 'role_id' de la tabla users
        return $this->role_id === 1;
    }

    /**
     * Comprueba si tiene un rol específico por nombre.
     */
    public function hasRole(string $roleName): bool
    {
        return optional($this->role)->name === $roleName;
    }


    /**
     * Recupera el usuario activo para una pestaña específica.
     * Busca en la sesión manual 'usuarios' y luego recupera el User fresco de la BD.
     *
     * @param string|null $sesionId El ID único de la pestaña/navegador.
     * @return User|null
     */
    public static function activeUserSesion(?string $sesionId): ?User
    {
        if (!$sesionId) {
            return null;
        }

        // Obtenemos el array global de usuarios conectados en este navegador
        $usuariosEnSesion = Session::get('usuarios', []);

        // Buscamos si existe una entrada para este $sesionId
        if (isset($usuariosEnSesion[$sesionId])) {
            $userData = json_decode($usuariosEnSesion[$sesionId]);

            // Recuperamos el usuario de la Base de Datos.
            // Esto asegura que si bloqueamos al usuario o cambiamos su rol,
            // la aplicación se entere inmediatamente.
            return User::with('role')->find($userData->id);
        }

        return null;
    }
}
