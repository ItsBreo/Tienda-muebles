<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Usamos firstOrCreate para evitar duplicados si se ejecuta varias veces.
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Gestor']);
        Role::firstOrCreate(['name' => 'Cliente']);
    }
}
