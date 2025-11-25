<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Clase Mueble
class Furniture extends model {

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    // Método helper para obtener la imagen principal
    public function getMainImage(): string {
        // Assuming you have an 'images' relationship that returns a collection of Image models
        $mainImage = $this->images()->where('is_primary', true)->first();
        if ($mainImage) {
            return $mainImage->image_path;
        }

        // Fallback to the first image if no main image is set
        $firstImage = $this->images()->orderBy('display_order')->first();
        if ($firstImage) {
            return $firstImage->image_path;
        }

        return 'default.jpg'; // Retornamos una imagen por defecto
    }

    /**
     * Devolvemos un array con los datos de prueba
     */
    public static function getMockData(): array {
        return [
            new self(['id' => 1, 'category_id' => 1, 'name' => "Mesa de Centro 'Nórdica'", 'description' => "Mesa de roble con diseño minimalista.", 'price' => 149.99, 'stock' => 5, 'materials' => "Madera de roble oscuro", 'dimensions' => "120cm x 60cm x 45cm", 'main_color' => "Roble Oscuro", 'is_salient' => true]),
            new self(['id' => 2, 'category_id' => 1, 'name' => "Sofá 'Confort'", 'description' => "Sofá de 3 plazas, tela gris.", 'price' => 399.99, 'stock' => 3, 'materials' => "Tela y madera", 'dimensions' => "200cm x 90cm x 85cm", 'main_color' => "Gris", 'is_salient' => false]),
            new self(['id' => 3, 'category_id' => 1, 'name' => "Estantería 'Lineal'", 'description' => "Estantería modular metálica.", 'price' => 89.50, 'stock' => 10, 'materials' => "Metal", 'dimensions' => "80cm x 30cm x 180cm", 'main_color' => "Negro", 'is_salient' => false]),
            new self(['id' => 4, 'category_id' => 2, 'name' => "Cama 'Queen'", 'description' => "Cama con cabecero tapizado.", 'price' => 299.00, 'stock' => 2, 'materials' => "Madera y tela", 'dimensions' => "160cm x 200cm", 'main_color' => "Beige", 'is_salient' => true]),
            new self(['id' => 5, 'category_id' => 3, 'name' => "Silla de Oficina 'Ergo'", 'description' => "Silla ergonómica con ruedas.", 'price' => 120.00, 'stock' => 15, 'materials' => "Plástico y malla", 'dimensions' => "60cm x 60cm x 110cm", 'main_color' => "Negro", 'is_salient' => true]),
            new self(['id' => 6, 'category_id' => 3, 'name' => "Escritorio 'Minimal'", 'description' => "Escritorio de madera clara y metal.", 'price' => 110.00, 'stock' => 6, 'materials' => "Madera de pino y metal", 'dimensions' => "140cm x 70cm x 75cm", 'main_color' => "Pino Claro", 'is_salient' => false]),
            new self(['id' => 7, 'category_id' => 4, 'name' => "Armario de Cocina 'Chef'", 'description' => "Módulo superior con 2 puertas.", 'price' => 75.50, 'stock' => 8, 'materials' => "Aglomerado", 'dimensions' => "80cm x 40cm x 60cm", 'main_color' => "Blanco", 'is_salient' => true]),
            new self(['id' => 8, 'category_id' => 4, 'name' => "Isla 'Gourmet'", 'description' => "Isla de cocina con almacenaje.", 'price' => 350.00, 'stock' => 3, 'materials' => "Granito y madera", 'dimensions' => "120cm x 80cm x 90cm", 'main_color' => "Blanco", 'is_salient' => true]),
            new self(['id' => 9, 'category_id' => 2, 'name' => "Mesita de Noche 'Clásica'", 'description' => "Mesita con 2 cajones.", 'price' => 60.00, 'stock' => 10, 'materials' => "Madera de pino", 'dimensions' => "40cm x 30cm x 55cm", 'main_color' => "Pino Claro", 'is_salient' => false]),
            new self(['id' => 10, 'category_id' => 1, 'name' => "Butaca 'Relax'", 'description' => "Butaca de lectura color mostaza.", 'price' => 180.00, 'stock' => 4, 'materials' => "Tela", 'dimensions' => "70cm x 80cm x 95cm", 'main_color' => "Mostaza", 'is_salient' => false]),
            new self(['id' => 11, 'category_id' => 2, 'name' => "Cama 'Nido'", 'description' => "Cama individual con cajones.", 'price' => 210.00, 'stock' => 7, 'materials' => "Madera de pino", 'dimensions' => "90cm x 200cm", 'main_color' => "Blanco", 'is_salient' => false]),
            new self(['id' => 12, 'category_id' => 4, 'name' => "Mesa de Cocina 'Extensible'", 'description' => "Mesa para 4-6 personas.", 'price' => 175.00, 'stock' => 3, 'materials' => "Madera y metal", 'dimensions' => "140cm (ext. 180cm) x 80cm", 'main_color' => "Pino Claro", 'is_salient' => true]),
        ];
    }

    /**
     * Buscamos un mueble por ID en los datos de prueba
     */
    public static function findById(int $id): ?Furniture {
        foreach (self::getMockData() as $furniture) {
            if ($furniture->id === $id) {
                return $furniture;
            }
        }
        return null;
    }

    /**
     * Formatea el precio del mueble según la moneda (solo simulación).
     *
     * @param string $moneda El código de moneda (ej. "EUR", "USD", "GBP").
     * @return string El precio formateado.
     */
    public function getFormattedPrice(string $moneda): string
    {
        // Definimos los símbolos
        $symbolMap = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
        ];

        // Buscamos el símbolo. Si no, usamos la moneda
        $simbolo = $symbolMap[$moneda] ?? $moneda;

        // Formateamos el precio
        $precioFormateado = number_format($this->price, 2, ',', '.');

        // Devolvemos el string final
        return $precioFormateado . ' ' . $simbolo;
    }
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function isSalient(): bool
    {
        return (bool) $this->is_salient;
    }
}
