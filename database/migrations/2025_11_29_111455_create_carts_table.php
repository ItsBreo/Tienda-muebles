<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('carts', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->onDelete('cascade');

			// CORRECCIÓN APLICADA: Añadimos la columna 'sesion_id' requerida por el controlador
			// La hacemos string y NOT NULL si el controlador garantiza su presencia,
			// pero la hacemos nullable por si hay sesiones anónimas.
			$table->string('sesion_id', 255)->nullable();

			// Total price debe estar presente y es NOT NULL según tu estructura de BD
			$table->decimal('total_price', 10, 2);

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('carts');
	}
};
