<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FurnitureController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GalleryController;

/*
| Gestión de muebles, categorías e imágenes.
| Base URL: http://api_furniture.test/api/
|
*/

Route::get('/furniture/featured', [FurnitureController::class, 'featured']);
Route::get('/furniture/colors',   [FurnitureController::class, 'colors']);

Route::apiResource('/furniture', FurnitureController::class);

Route::prefix('/furniture/{mueble}/gallery')->name('gallery.')->group(function () {
    Route::post('/',              [GalleryController::class, 'store'])->name('store');
    Route::delete('/{image}',     [GalleryController::class, 'destroy'])->name('destroy');
    Route::post('/{image}/main',  [GalleryController::class, 'setMain'])->name('main');
});

Route::apiResource('/categories', CategoryController::class);
