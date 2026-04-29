<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Furniture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Furniture>
 */
class FurnitureFactory extends Factory
{
    protected $model = Furniture::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'name'        => 'Mesa de ' . $this->faker->words(2, true),
            'description' => $this->faker->paragraph(3),
            'materials'   => $this->faker->randomElement(['Roble macizo', 'Metal y cristal', 'Pino reciclado', 'Terciopelo']),
            'dimensions'  => $this->faker->numberBetween(80, 200) . 'x' . $this->faker->numberBetween(50, 150) . 'x' . $this->faker->numberBetween(40, 100) . ' cm',
            'main_color'  => $this->faker->safeColorName,
            'price'       => $this->faker->randomFloat(2, 50, 2500),
            'stock'       => $this->faker->numberBetween(0, 50),
            'is_salient'  => $this->faker->boolean(20),
        ];
    }
}
