<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Furniture extends Model // Nota: Model con mayúscula
{
    use HasFactory;

    // 1. Asignación masiva (Coincide con tus columnas snake_case de la DB)
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'materials',
        'dimensions',
        'main_color',
        'is_salient',
    ];

    // 2. Relaciones: Un mueble tiene muchas imágenes
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    // 3. Helper para saber si es destacado (Casteo simple)
    public function isSalient(): bool
    {
        return (bool) $this->is_salient;
    }

    /**
     * 4. Helper para obtener la imagen principal.
     * ADVERTENCIA: Para que esto funcione, tu tabla 'images' debe tener
     * columnas 'is_primary' (booleano) y 'url' (string).
     */
    public function getMainImage(): string{

        // Encontramos la imagen principal
        $mainImage = $this->images()->firstwhere('is_primary', true);

        // Si existe retornamos la ruta donde está la imagen
        if ($mainImage) {
            return $mainImage->image_path;
        }

        // Si no hay primaria, toma la primera que encuentre
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_path;
        }

        // Si no tiene ninguna foto subida, devuelve la default
        return 'images/default.png';
    }

    /**
     * 5. Formateador de precio (Presentación)
     */
    public function getFormattedPrice(string $moneda = 'EUR'): string
    {
        $symbolMap = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
        ];

        $simbolo = $symbolMap[$moneda] ?? $moneda;

        // Formatea el número (ej: 1.250,50)
        $precioFormateado = number_format($this->price, 2, ',', '.');

        return $precioFormateado . ' ' . $simbolo;
    }
}
