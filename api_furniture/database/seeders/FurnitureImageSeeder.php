<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Image;
use Illuminate\Support\Facades\DB;

class FurnitureImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Image::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
            for ($i = 1; $i <= 3; $i++) {
                $dataToInsert[] = [
                    'furniture_id'  => $id,
                    'image_path'    => "images/{$prefix}_{$i}.png",
                    'is_primary'    => ($i === 1),
                    'display_order' => $i,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        Image::insert($dataToInsert);
    }
}
