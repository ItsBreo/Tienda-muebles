<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'     => $this->faker->name,
            'surname'  => $this->faker->lastName,
            'email'    => $this->faker->unique()->safeEmail,
            'password' => Hash::make('1234'),
            'role_id'  => $this->faker->randomElement([2, 3]), // 2: Gestor, 3: Cliente
        ];
    }
}
