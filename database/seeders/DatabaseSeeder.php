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
        // Ejecutar el seeder de roles primero
        $this->call(RoleSeeder::class);

        // Buscamos el rol 'Admin' para obtener su ID dinámicamente.
        $adminRole = Role::where('name', 'Admin')->first();

        // Crear un usuario Administrador
        User::factory()->create([
            'name' => 'Admin',
            'surname' => 'User',
            'email' => 'admin@tienda.com',
            'password' => bcrypt('1234'),
            // Asignamos el ID del rol que acabamos de encontrar.
            'role_id' => $adminRole->id,
        ]);

        User::factory()->create([
            'name' => 'Usuario',
            'surname' => 'Prueba',
            'email' => 'usuario@tienda.com',
            'password' => bcrypt('1234'),
            'role_id' => 3,
        ]);

        // Crear 50 usuarios de prueba (Gestores y Clientes)
        User::factory(50)->create();

        // Ejecutar los seeders de categorías y muebles
        $this->call([
            CategorySeeder::class,
            FurnitureSeeder::class,
            FurnitureImageSeeder::class,
            CartSeeder::class
        ]);
    }
}
