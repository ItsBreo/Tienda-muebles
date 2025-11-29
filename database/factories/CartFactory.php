<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
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
            // El carrito pertenece a un usuario (se crea uno si no se especifica)
            'user_id' => User::factory(),

            // Nuevo campo: Generamos una cadena única simulando una ID de sesión
            'sesion_id' => $this->faker->uuid(),

            // Precio total entre 0 y 1000 con 2 decimales
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
