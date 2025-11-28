<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class FurnitureImageSeeder extends Seeder
{
    public function run(): void
    {
        // Desactivamos FK checks para limpiar la tabla sin problemas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Image::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Mapa: ID del Mueble => Prefijo del archivo de imagen
        $furnitureMap = [
            1  => 'mesa_nordica',
            2  => 'sofa_confort',
            3  => 'estanteria_lineal',
            4  => 'cama_queen',
            5  => 'silla_ergo',
            6  => 'escritorio_minimal',
            7  => 'armario_chef',
            8  => 'isla_gourmet',
            9  => 'mesita_clasica',
            10 => 'butaca_relax',
            11 => 'cama_nido',
            12 => 'mesa_cocina',
        ];
        $dataToInsert = [];
        $now = now();
        foreach ($furnitureMap as $id => $prefix) {
            // Generamos las 3 imágenes para cada mueble
            for ($i = 1; $i <= 3; $i++) {
                $dataToInsert[] = [
                    'furniture_id'  => $id,
                    // Ruta relativa desde 'public'
                    'image_path'    => "images/{$prefix}_{$i}.png",
                    'is_primary'    => ($i === 1), // La imagen _1 será la principal
                    'display_order' => $i,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }
        // Insertamos en lote para optimizar rendimiento
        Image::insert($dataToInsert);
    }
}
