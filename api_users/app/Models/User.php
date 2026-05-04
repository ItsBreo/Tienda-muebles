<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'locked_until'  => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function sessionLogs()
    {
        return $this->hasMany(SessionLog::class);
    }

    /**
     * Comprueba si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role_id === 1;
    }

    /**
     * Comprueba si tiene un rol específico por nombre.
     */
    public function hasRole(string $roleName): bool
    {
        return optional($this->role)->name === $roleName;
    }
}
