<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PreferenciasController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\MuebleController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ProductosGaleriaController;
use Illuminate\Http\Request;

// Página Principal
 Route::get('/', [PrincipalController::class, 'index'])->name('principal');

// Login (Sesiones):
 Route::get('/login', [LoginController::class, 'show'])->name('login.show');
 Route::post('/login', [LoginController::class, 'login'])->name('login.store');
 Route::post('/logout', [LoginController::class, 'logout'])->name('login.logout');

// Preferencias (Cookies)
 Route::get('/preferencias', [PreferenciasController::class, 'show'])->name('preferencias.show');
 Route::post('/preferencias', [PreferenciasController::class, 'update'])->name('preferencias.update');

// Catálogo: muebles (listado + detalle)
 Route::get('/muebles', [CatalogoController::class, 'index'])->name('muebles.index');
 Route::get('/mueble/{id}', [MuebleController::class, 'show'])->name('muebles.show');

 // Catálogo: categorías
 Route::get('/categorias', [CatalogoController::class, 'categorias'])->name('categorias.index');
 Route::get('/categoria/{id}', [CatalogoController::class, 'show'])->name('categorias.show');

// Carrito:
 Route::get('/carrito', [CarritoController::class, 'show'])->name('carrito.show');
 Route::post('/carrito/insertar/{mueble}', [CarritoController::class, 'add'])->name('carrito.add');
 Route::post('/carrito/actualizar/{mueble}', [CarritoController::class, 'update'])->name('carrito.update');
 Route::post('/carrito/eliminar/{mueble}', [CarritoController::class, 'remove'])->name('carrito.remove');
 Route::post('/carrito/vaciar', [CarritoController::class, 'clear'])->name('carrito.clear');

// Panel de Administración (Solo usuario rol ADMIN)
// CRUD de Muebles (con cookies)
Route::get('/admin/muebles', [AdministracionController::class, 'index'])->name('admin.muebles.index');
Route::get('/admin/muebles/crear', [AdministracionController::class, 'create'])->name('admin.muebles.create');
Route::post('/admin/muebles', [AdministracionController::class, 'store'])->name('admin.muebles.store');
Route::get('/admin/muebles/{id}', [AdministracionController::class, 'show'])->name('admin.muebles.show');
Route::get('/admin/muebles/{id}/editar', [AdministracionController::class, 'edit'])->name('admin.muebles.edit');
Route::put('/admin/muebles/{id}', [AdministracionController::class, 'update'])->name('admin.muebles.update');
Route::delete('/admin/muebles/{id}', [AdministracionController::class, 'destroy'])->name('admin.muebles.destroy');

// Categorías (CRUD)
 //Route::resource('categorias', CatalogoController::class);

// Nombres generados:
// categorias.index|create|store|show|edit|update|destroy
// Productos (CRUD)
 Route::resource('productos', ProductosController::class);

// Nombres generados:
// productos.index|create|store|show|edit|update|destroy
// Galería de Productos
 Route::post('productos/{mueble}/galeria', [ProductosGaleriaController::class,'store'])->name('productos.galeria.store');// Subida múltiple

 Route::post('productos/{mueble}/galeria/{image}',
[ProductosGaleriaController::class, 'destroy'])->name('productos.galeria.destroy'); // Borrar imagen

 Route::post('productos/{mueble}/galeria/{image}/principal',
[ProductosGaleriaController::class, 'setMain'])->name('productos.galeria.principal'); // Establecer imagen principal

// Panel de Administración de prueba
// TODO: Borrar esta ruta una vez el CRUD esté hecho
Route::view('/admin', 'adminPanel')->name('admin.dashboard');

// Depuración de cookies
Route::get('/cookiesActivas', function (Request $request) {

    $cookies = $request->cookies->all();

    echo "<h3>Cookies detectadas por Request:</h3>";
    dd($cookies);
});
