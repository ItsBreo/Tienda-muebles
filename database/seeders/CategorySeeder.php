<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void{

    for ($i = 1; $i < 5; $i++) {
        Category::factory()->create([
            'nombre' => 'Categoria' . $i
        ]);
    }
}
}
