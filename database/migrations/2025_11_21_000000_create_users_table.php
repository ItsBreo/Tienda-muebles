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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', length:255);
            $table->string('surname', length:255);
            $table->string('email', length: 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            // Usamos una clave foránea para el rol
            $table->foreignId('role_id')->constrained('roles')->default(3); // Por defecto: Cliente

            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

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
