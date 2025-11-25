<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', length:255);
            $table->string('apellidos', length:255);
            $table->string('email', length: 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // Usamos una clave foránea para el rol
            $table->foreignId('role_id')->constrained('roles')->default(3); // Por defecto: Cliente
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuarios');
    }
}
