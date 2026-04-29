<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

/*
| Autenticación y gestión de usuarios.
| Base URL: http://api_users.test/api/
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/logout',   [AuthController::class, 'logout']);
Route::get('/session/{session_id}', [AuthController::class, 'sessionCheck']);

Route::get('/roles', [UserController::class, 'roles']);
Route::apiResource('/users', UserController::class);
