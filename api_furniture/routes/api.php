<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FurnitureController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Middleware\LogUserActivity;

/*
|--------------------------------------------------------------------------
| API Routes — api_furniture
|--------------------------------------------------------------------------
|
| Base URL: http://127.0.0.1:8002/api/
|
| Rutas públicas: GET furniture, GET furniture/{id}, GET categories, colores, destacados
| Rutas de escritura: POST/PUT/DELETE (pendientes de protección con token)
|
*/

// ── Muebles: rutas especiales ANTES del resource ──────────────────────────────
Route::get('/furniture/featured', [FurnitureController::class, 'featured']);
Route::get('/furniture/colors',   [FurnitureController::class, 'colors']);
Route::post('/furniture/decrement-stock', [FurnitureController::class, 'decrementStock']);

// ── CRUD completo de muebles (Lectura pública) ──────────────────────────────────
Route::apiResource('/furniture', FurnitureController::class)->only(['index', 'show']);

// ── CRUD completo de categorías (Lectura pública) ───────────────────────────────
Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']);

// ── Rutas protegidas (Requieren token y habilidades) ──────────────────────────
Route::middleware(['remote.auth:muebles.crear', LogUserActivity::class])->group(function () {
    Route::post('/furniture', [FurnitureController::class, 'store'])->name('furniture.store');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/furniture/{mueble}/gallery', [GalleryController::class, 'store'])->name('gallery.store');
});

Route::middleware(['remote.auth:muebles.editar', LogUserActivity::class])->group(function () {
    Route::put('/furniture/{furniture}', [FurnitureController::class, 'update'])->name('furniture.update');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/furniture/{mueble}/gallery/{image}/main', [GalleryController::class, 'setMain'])->name('gallery.setMain');
    Route::put('/furniture/{mueble}/gallery/{image}/order', [GalleryController::class, 'updateOrder'])->name('gallery.order');
});

Route::middleware(['remote.auth:muebles.eliminar', LogUserActivity::class])->group(function () {
    Route::delete('/furniture/{furniture}', [FurnitureController::class, 'destroy'])->name('furniture.destroy');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::delete('/furniture/{mueble}/gallery/{image}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
});

// ── Activity Logs (Admin Panel) ───────────────────────────────────────────────
Route::middleware('remote.auth:admin.panel')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});
