<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FurnitureController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GalleryController;

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

// ── CRUD completo de muebles ──────────────────────────────────────────────────
Route::apiResource('/furniture', FurnitureController::class);

// ── Galería de imágenes ───────────────────────────────────────────────────────
Route::prefix('/furniture/{mueble}/gallery')->name('gallery.')->group(function () {
    Route::post('/',                    [GalleryController::class, 'store'])->name('store');
    Route::delete('/{image}',           [GalleryController::class, 'destroy'])->name('destroy');
    Route::post('/{image}/main',        [GalleryController::class, 'setMain'])->name('main');
    Route::put('/{image}/order',        [GalleryController::class, 'updateOrder'])->name('order');
});

// ── CRUD completo de categorías ───────────────────────────────────────────────
Route::apiResource('/categories', CategoryController::class);
