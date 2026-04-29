<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear roles
        $this->call(RoleSeeder::class);

        // 2. Buscar rol Admin (ID dinámico)
        $adminRole = Role::where('name', 'Admin')->first();

        // 3. Crear usuario Administrador
        User::factory()->create([
            'name'     => 'Admin',
            'surname'  => 'User',
            'email'    => 'admin@tienda.com',
            'password' => bcrypt('1234'),
            'role_id'  => $adminRole->id,
        ]);

        // 4. Crear usuario de prueba (Cliente)
        User::factory()->create([
            'name'     => 'Usuario',
            'surname'  => 'Prueba',
            'email'    => 'usuario@tienda.com',
            'password' => bcrypt('1234'),
            'role_id'  => 3,
        ]);

        // 5. Crear 50 usuarios de prueba
        User::factory(50)->create();
    }
}
