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

    Schema::create('session_logs', function (Blueprint $table) {
        $table->id();
        $table->string('session_id')->unique()->comment('ID de sesión');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->ipAddress('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamp('login_at');
        $table->timestamp('logout_at')->nullable();
        $table->timestamps();
    });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_logs');
    }
};
