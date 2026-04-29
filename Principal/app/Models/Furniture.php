<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Furniture extends Model
{
    use HasFactory;

    // Atributos de la tabla 'furniture'
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

    // Relaciones: Un mueble pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relaciones: Un mueble tiene muchas imágenes 1:N
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    // Helper para saber si es destacado
    public function isSalient(): bool
    {
        return (bool) $this->is_salient;
    }

    /**
    * Helper para obtener la imagen principal.
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
     * Formateador de precio (Presentación)
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
