<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

/*
| API REST de Usuarios
| Base URL: http://api_users.test/api/
|
| Rutas públicas  : register, login, session check (servicio a servicio)
| Rutas protegidas: requieren Bearer token emitido por Sanctum en el login
*/

// ── Públicas ──────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Endpoint de verificación de sesión para consumo interno entre servicios
Route::get('/session/{session_id}', [AuthController::class, 'sessionCheck']);

// ── Protegidas por Sanctum ────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Perfil y cierre de sesión (abilities: profile:view / profile:update)
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Gestión de roles (ability: roles:list — solo Admin)
    Route::get('/roles', [UserController::class, 'roles']);

    // CRUD de usuarios (abilities: users:* — solo Admin, salvo ver/editar el propio)
    Route::apiResource('/users', UserController::class);
});
