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
        Schema::create('cart_furniture', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('furniture_id')->constrained('furniture')->onDelete('cascade');

            // CORRECCIÓN 1: Usamos 'quantity' (SnakeCase en inglés)
            $table->integer('quantity');

            // CORRECCIÓN 2: Añadimos el campo de precio unitario que faltaba (Requisito del PDF)
            $table->decimal('unit_price', 10, 2)->comment('Precio del producto en el momento de la compra');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_furniture');
    }
};
