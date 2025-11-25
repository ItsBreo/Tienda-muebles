<?php
public function run(): void{
        // User::factory(10)->create();

    for ($i = 1; $i < 5; $i++) {
        Categoria::factory()->create([
            'nombre' => 'Categoria' . $i
        ]);
    }
}
