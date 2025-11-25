<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Clase Imagen
class Image extends model {
    use HasFactory;

    // Define las columnas que se pueden llenar masivamente
    protected $fillable = [
        'furniture_id',
        'url',
        'order',
        'is_main',
        'alt_text',
    ];

    public function furniture()
    {
        // Una imagen pertenece a un solo mueble (muchos a uno)
        return $this->belongsTo(Furniture::class);
    }
}
