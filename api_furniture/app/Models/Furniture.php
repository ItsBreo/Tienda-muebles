<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Furniture extends Model
{
    use HasFactory;

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

    // Un mueble pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Un mueble tiene muchas imágenes
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function isSalient(): bool
    {
        return (bool) $this->is_salient;
    }

    /**
     * Devuelve la ruta de la imagen principal.
     */
    public function getMainImage(): string
    {
        $mainImage = $this->images()->firstwhere('is_primary', true);
        if ($mainImage) {
            return $mainImage->image_path;
        }

        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_path;
        }

        return 'images/default.png';
    }

    /**
     * Formatea el precio según la moneda.
     */
    public function getFormattedPrice(string $moneda = 'EUR'): string
    {
        $symbolMap = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
        ];

        $simbolo = $symbolMap[$moneda] ?? $moneda;
        $precioFormateado = number_format($this->price, 2, ',', '.');

        return $precioFormateado . ' ' . $simbolo;
    }
}
