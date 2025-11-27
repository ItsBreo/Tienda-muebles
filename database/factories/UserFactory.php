<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /* The name of the factory's corresponding model.
     *s
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'surname' => $this->faker->paragraph(2),
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('1234'), // Añadimos una contraseña por defecto
            'role_id' => $this->faker->randomElement([2, 3]), // 2: Gestor, 3: Cliente
        ];
    }
}
