<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;

/*
| Esta aplicación es el cliente principal que consume las APIs:
*/

Route::get('/', [PrincipalController::class, 'index'])->name('principal');

// ── Autenticación ───────────────────────────────────────────────────────────
Route::get('/login',    [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login',   [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
Route::post('/register',[AuthController::class, 'register'])->name('register.submit');
Route::post('/logout',  [AuthController::class, 'logout'])->name('login.logout');

Route::get('/catalogo', [CatalogoController::class, 'index'])->name('muebles.index');
Route::get('/catalogo/{id}', [CatalogoController::class, 'showMueble'])->name('muebles.show');

Route::get('/categorias', [CatalogoController::class, 'indexCategorias'])->name('categorias.index');
Route::get('/categorias/{id}', [CatalogoController::class, 'showCategoria'])->name('categorias.show');

Route::get('/preferencias',  [PreferenciasController::class, 'show'])->name('preferencias.show');
Route::post('/preferencias', [PreferenciasController::class, 'update'])->name('preferencias.update');

Route::get('/carrito',                       [CarritoController::class, 'show'])->name('carrito.show');
Route::post('/carrito/insertar/{mueble}',    [CarritoController::class, 'add'])->name('carrito.add');
Route::post('/carrito/actualizar/{mueble}',  [CarritoController::class, 'update'])->name('carrito.update');
Route::post('/carrito/eliminar/{mueble}',    [CarritoController::class, 'remove'])->name('carrito.remove');
Route::post('/carrito/vaciar',               [CarritoController::class, 'clear'])->name('carrito.clear');
Route::post('/carrito/guardar',              [CarritoController::class, 'saveOnBD'])->name('carrito.save');

// ── Depuración de cookies (desarrollo) ───────────────────────────────────────
Route::get('/cookiesActivas', function (Request $request) {
    $cookies = $request->cookies->all();
    echo "<h3>Cookies detectadas por Request:</h3>";
    dd($cookies);
});
