<?php

namespace Database\Seeders;

use App\Models\Cart;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 * @return void
	 */
	public function run()
	{
		// Esto ahora usa la CartFactory actualizada con 'sesion_id'
		Cart::factory()->count(5)->create();
	}
}
