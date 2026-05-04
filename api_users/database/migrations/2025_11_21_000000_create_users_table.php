<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname', 255)->nullable()->after('name');
            $table->unsignedBigInteger('role_id')->nullable()->after('password');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->integer('failed_attempts')->default(0)->after('role_id');
            $table->timestamp('locked_until')->nullable()->after('failed_attempts');
            $table->timestamp('last_login_at')->nullable()->after('locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['surname', 'role_id', 'failed_attempts', 'locked_until', 'last_login_at']);
        });
    }
};
