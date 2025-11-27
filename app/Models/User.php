<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // ¡Importante para Auth!
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'surname',      // Añadido según migración
        'email',
        'password',
        'role_id',
        'failed_attempts', // Para el bloqueo de seguridad
        'locked_until',    // Para el bloqueo de seguridad
    ];

    /**
     * Los atributos ocultos.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión de tipos.
     */
    protected $casts = [
        'locked_until' => 'datetime',
    ];

    // ------------------------------------------------------------------------
    // RELACIONES
    // ------------------------------------------------------------------------

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // ------------------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------------------

    /**
     * Comprueba si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        // Usamos optional() por si la relación role no está cargada o es null
        return optional($this->role)->name === 'Administrador' || optional($this->role)->name === 'admin';
    }

    /**
     * Comprueba si tiene un rol específico por nombre.
     */
    public function hasRole(string $roleName): bool
    {
        return optional($this->role)->name === $roleName;
    }

    // ------------------------------------------------------------------------
    // LÓGICA DE SESIÓN MANUAL (Requisito: Múltiples usuarios en el mismo navegador)
    // ------------------------------------------------------------------------

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

        // 1. Obtenemos el array global de usuarios conectados en este navegador
        $usuariosEnSesion = Session::get('usuarios', []);

        // 2. Buscamos si existe una entrada para este $sesionId
        if (isset($usuariosEnSesion[$sesionId])) {
            $userData = json_decode($usuariosEnSesion[$sesionId]);

            // 3. ¡IMPORTANTE! Recuperamos el usuario de la Base de Datos.
            // Esto asegura que si bloqueamos al usuario o cambiamos su rol,
            // la aplicación se entere inmediatamente.
            return User::with('role')->find($userData->id);
        }

        return null;
    }
}
