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
        Schema::create('furniture', function (Blueprint $table) {
            $table->id();
            // Relación con Categorías
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');

            // Campos de la tabla Muebles
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->string('materials');
            $table->string('dimensions');
            $table->string('main_color');
            $table->boolean('is_salient')->default(false);
            $table->json('images'); // Almacenamos las rutas de las imágenes en formato JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('furniture');
    }
};
