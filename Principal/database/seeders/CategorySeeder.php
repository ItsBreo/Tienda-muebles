<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Usamos firstOrCreate para evitar duplicados.
        Category::firstOrCreate(['name' => 'Salón', 'description' => 'Productos para el salón']);
        Category::firstOrCreate(['name' => 'Dormitorio', 'description' => 'Productos para el dormitorio']);
        Category::firstOrCreate(['name' => 'Oficina', 'description' => 'Productos para la oficina']);
        Category::firstOrCreate(['name' => 'Cocina', 'description' => 'Productos para la cocina']);
        Category::firstOrCreate(['name' => 'Baño', 'description' => 'Productos para el baño']);
    }
}
