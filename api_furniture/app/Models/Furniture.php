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

    /**
     * Casts automáticos para que los tipos sean correctos en JSON.
     */
    protected $casts = [
        'price'      => 'float',
        'stock'      => 'integer',
        'is_salient' => 'boolean',
    ];

    // ── Relaciones ─────────────────────────────────────────────────────────────

    /** Un mueble pertenece a una categoría. */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Un mueble tiene muchas imágenes (ordenadas: principal primero). */
    public function images()
    {
        return $this->hasMany(Image::class)
                    ->orderByDesc('is_primary')
                    ->orderBy('display_order');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Devuelve la ruta de la imagen principal.
     * Usa la relación en memoria si ya fue cargada (evita N+1).
     */
    public function getMainImage(): string
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            $primary = $this->images->firstWhere('is_primary', true);
            return $primary ? $primary->image_path : $this->images->first()->image_path;
        }

        // Fallback con query directa (cuando no está cargada la relación)
        $mainImage = $this->images()->firstWhere('is_primary', true);
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
     * Formatea el precio con símbolo de moneda.
     */
    public function getFormattedPrice(string $moneda = 'EUR'): string
    {
        $symbolMap = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
        ];

        $simbolo          = $symbolMap[$moneda] ?? $moneda;
        $precioFormateado = number_format($this->price, 2, ',', '.');

        return $precioFormateado . ' ' . $simbolo;
    }
}
