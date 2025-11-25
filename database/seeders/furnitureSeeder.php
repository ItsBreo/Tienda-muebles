<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Furniture; // Asegúrate de importar tu modelo Mueble
use App\Models\Image;     // Asegúrate de importar tu modelo Imagen

class FurnitureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Crear 50 muebles y, por cada mueble, crear 4 imágenes asociadas.
        Furniture::factory()
            ->count(50)

            // Usamos el método has() para vincular la relación 'images'
            // 1. Creamos 1 imagen obligatoria y la forzamos a ser la principal
            ->has(
                Image::factory()->state(['is_main' => true, 'order' => 1])->count(1),
                'images' // Nombre del método de relación en el modelo Furniture
            )
            // 2. Creamos 3 imágenes adicionales y las forzamos a NO ser principal
            ->has(
                Image::factory()->state(['is_main' => false, 'order' => 2])->count(2),
                'images'
            )
            ->create();
    }
}
