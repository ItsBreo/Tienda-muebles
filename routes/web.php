<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Ruta para mostrar el formulario de login
Route::get('/login-local', function () {
    return view('auth.login_local');
})->name('login.local');
