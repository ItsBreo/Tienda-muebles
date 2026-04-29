<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Furniture;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class furnitureImageFactory extends Factory
{
 /* The name of the factory's corresponding model.
     *s
     * @var string
     */
    protected $model = Image::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [

            'furniture_id' => Furniture::inRandomOrder()->first()->id, // Eleccion aleatoria de id de muebles

            // Atributos de Cadena (string)
            'alt_text' => 'Imagen ' . $this->faker->words(2, true), // Genera nombres como "Mesa de Roble Moderna"
            'image_path' => $this->faker->url(), // URL falsa

            // 4. Atributos Numéricos (float, int)
           'display_order' => $this->faker->numberBetween(1, 3),

            // 5. Atributo Booleano (bool)
            'is_primary' => $this->faker->boolean(25), // 25% de probabilidad de ser "primaria" (TRUE)
        ];
    }
}
