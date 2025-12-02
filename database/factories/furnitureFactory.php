<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use App\Models\Furniture;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Furniture>
 */
class furnitureFactory extends Factory
{
    /* The name of the factory's corresponding model.
     *s
     * @var string
     */
    protected $model = Furniture::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id, // Eleccion aleatoria de id de categorias

            // Atributos de Cadena (string)
            'name' => 'Mesa de ' . $this->faker->words(2, true), // Genera nombres como "Mesa de Roble Moderna"
            'description' => $this->faker->paragraph(3), // Texto largo para descripción
            'materials' => $this->faker->randomElement(['Roble macizo', 'Metal y cristal', 'Pino reciclado', 'Terciopelo']),
            'dimensions' => $this->faker->numberBetween(80, 200) . 'x' . $this->faker->numberBetween(50, 150) . 'x' . $this->faker->numberBetween(40, 100) . ' cm', // Ejemplo: 120x80x45 cm
            'main_color' => $this->faker->safeColorName, // Ejemplo: 'blue', 'brown', 'maroon'

            // Atributos Numéricos (float, int)
            'price' => $this->faker->randomFloat(2, 50, 2500), // Precio entre 50.00 y 2500.00 con 2 decimales
            'stock' => $this->faker->numberBetween(0, 50), // Stock entre 0 y 50

            // Atributo Booleano (bool)
            'is_salient' => $this->faker->boolean(20), // 20% de probabilidad de ser "Destacado" (TRUE)
        ];
    }

}
