<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Usamos firstOrCreate para evitar duplicados si se ejecuta varias veces.
        Category::firstOrCreate(['name' => 'Categoria 1']);
        Category::firstOrCreate(['name' => 'Categoria 2']);
        Category::firstOrCreate(['name' => 'Categoria 3']);
        Category::firstOrCreate(['name' => 'Categoria 4']);
        Category::firstOrCreate(['name' => 'Categoria 5']);
    }
}
