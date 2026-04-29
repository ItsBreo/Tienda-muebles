<?php

namespace Database\Factories;

use App\Models\Furniture;
use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class FurnitureImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition(): array
    {
        return [
            'furniture_id'  => Furniture::inRandomOrder()->first()->id,
            'alt_text'      => 'Imagen ' . $this->faker->words(2, true),
            'image_path'    => 'images/' . $this->faker->slug(2) . '.png',
            'display_order' => $this->faker->numberBetween(1, 3),
            'is_primary'    => $this->faker->boolean(25),
        ];
    }
}
