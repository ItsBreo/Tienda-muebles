<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Clase Imagen
class Image extends model {
    use HasFactory;

    protected $table = 'furniture_images';

    // Define las columnas que se pueden llenar masivamente
    protected $fillable = [
        'furniture_id',
        'image_path',
        'is_primary',
        'display_order',
        'alt_text',
    ];

    public function furniture()
    {
        // Una imagen pertenece a un solo mueble (muchos a uno)
        return $this->belongsTo(Furniture::class);
    }
}
