<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    use HasFactory;

    protected $table = 'session_logs';


    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'login_at',
        'logout_at',
    ];


    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
