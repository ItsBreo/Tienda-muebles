<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PerfilController;
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
Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.show');

Route::get('/carrito',                       [CarritoController::class, 'show'])->name('carrito.show');
Route::post('/carrito/insertar/{mueble}',    [CarritoController::class, 'add'])->name('carrito.add');
Route::post('/carrito/actualizar/{mueble}',  [CarritoController::class, 'update'])->name('carrito.update');
Route::post('/carrito/eliminar/{mueble}',    [CarritoController::class, 'remove'])->name('carrito.remove');
Route::post('/carrito/vaciar',               [CarritoController::class, 'clear'])->name('carrito.clear');
Route::post('/carrito/guardar',              [CarritoController::class, 'saveOnBD'])->name('carrito.save');

// ── Stripe Checkout ─────────────────────────────────────────────────────────
Route::post('/carrito/checkout', [CarritoController::class, 'stripeCheckout'])->name('carrito.checkout');
Route::get('/checkout/success',  [CarritoController::class, 'checkoutSuccess'])->name('checkout.success');
Route::get('/checkout/cancel',   [CarritoController::class, 'checkoutCancel'])->name('checkout.cancel');

// ── Panel de Administración (solo rol Admin) ──────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware('es.admin')->group(function () {

    // Muebles
    Route::get('/muebles',              [AdminController::class, 'mueblesIndex'])->name('muebles.index');
    Route::get('/muebles/crear',        [AdminController::class, 'mueblesCreate'])->name('muebles.create');
    Route::post('/muebles',             [AdminController::class, 'mueblesStore'])->name('muebles.store');
    Route::get('/muebles/{mueble}',     [AdminController::class, 'mueblesShow'])->name('muebles.show');
    Route::get('/muebles/{mueble}/editar', [AdminController::class, 'mueblesEdit'])->name('muebles.edit');
    Route::put('/muebles/{mueble}',     [AdminController::class, 'mueblesUpdate'])->name('muebles.update');
    Route::delete('/muebles/{mueble}',  [AdminController::class, 'mueblesDestroy'])->name('muebles.destroy');

    // Categorías
    Route::get('/categorias',              [AdminController::class, 'categoriasIndex'])->name('categorias.index');
    Route::get('/categorias/crear',        [AdminController::class, 'categoriasCreate'])->name('categorias.create');
    Route::post('/categorias',             [AdminController::class, 'categoriasStore'])->name('categorias.store');
    Route::get('/categorias/{categoria}',  [AdminController::class, 'categoriasShow'])->name('categorias.show');
    Route::get('/categorias/{categoria}/editar', [AdminController::class, 'categoriasEdit'])->name('categorias.edit');
    Route::put('/categorias/{categoria}',  [AdminController::class, 'categoriasUpdate'])->name('categorias.update');
    Route::delete('/categorias/{categoria}', [AdminController::class, 'categoriasDestroy'])->name('categorias.destroy');

    // Usuarios (solo lectura)
    Route::get('/usuarios', [AdminController::class, 'usuariosIndex'])->name('usuarios.index');

    // Activity Logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
});

// ── Depuración de cookies (desarrollo) ───────────────────────────────────────
Route::get('/cookiesActivas', function (Request $request) {
    $cookies = $request->cookies->all();
    echo "<h3>Cookies detectadas por Request:</h3>";
    dd($cookies);
});
