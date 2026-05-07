<?php

namespace Database\Factories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Cada usuario tiene un solo carrito
            'user_id' => $this->faker->numberBetween(1, 10),

            'sesion_id' => $this->faker->uuid(),

            // Precio total entre 0 y 1000
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
