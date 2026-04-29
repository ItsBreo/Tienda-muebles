<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\CarritoController;
use Illuminate\Http\Request;

/*
| Esta aplicación es el cliente principal que consume las APIs:
|   - api_users  (http://api_users.test)
|   - api_furniture (http://api_furniture.test)
*/

Route::get('/', [PrincipalController::class, 'index'])->name('principal');

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
