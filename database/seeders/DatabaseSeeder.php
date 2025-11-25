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
        // 1. Ejecutar el seeder de roles primero
        $this->call(RoleSeeder::class);

        // Buscamos el rol 'Admin' para obtener su ID dinámicamente.
        $adminRole = Role::where('name', 'Admin')->first();

        // 2. Crear un usuario Administrador
        User::factory()->create([
            'nombre' => 'Admin',
            'apellidos' => 'User',
            'email' => 'admin@tienda.com',
            'password' => bcrypt('1234'),
            // Asignamos el ID del rol que acabamos de encontrar.
            'role_id' => $adminRole->id,
        ]);

        // 3. Crear 50 usuarios de prueba (Gestores y Clientes)
        User::factory(50)->create();

        // 4. Ejecutar los seeders de categorías y muebles
        $this->call([
            CategorySeeder::class,
            FurnitureSeeder::class,
        ]);
    }
}
