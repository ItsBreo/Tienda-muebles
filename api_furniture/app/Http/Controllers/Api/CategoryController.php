<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/categories
    // Lista todas las categorías.
    // ──────────────────────────────────────────────────────────────────────────
    public function index(): JsonResponse
    {
        $categorias = Category::withCount('furniture')
            ->orderBy('name')
            ->get();

        return $this->okResponse(
            CategoryResource::collection($categorias)->resolve(request()),
            'Categorías disponibles.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/categories/{id}
    // Detalle de una categoría con sus muebles.
    // ──────────────────────────────────────────────────────────────────────────
    public function show(Category $categoria): JsonResponse
    {
        $categoria->load([
            'furniture.images',
        ])->loadCount('furniture');

        return $this->okResponse(
            new CategoryResource($categoria),
            'Detalle de categoría.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/categories
    // Crea una nueva categoría. Requiere token de admin.
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $categoria = Category::create($validated);

        return $this->createdResponse(
            new CategoryResource($categoria),
            'Categoría creada correctamente.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUT /api/categories/{id}
    // Actualiza una categoría. Requiere token de admin.
    // ──────────────────────────────────────────────────────────────────────────
    public function update(Request $request, Category $categoria): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $categoria->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $categoria->update($validated);

        return $this->okResponse(
            new CategoryResource($categoria->fresh()),
            'Categoría actualizada correctamente.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/categories/{id}
    // Elimina una categoría. Requiere token de admin.
    //
    // Nota: los muebles asociados quedarán con category_id = NULL
    // (definido con onDelete('set null') en la FK de furniture).
    // ──────────────────────────────────────────────────────────────────────────
    public function destroy(Category $categoria): JsonResponse
    {
        $nombre = $categoria->name;
        $categoria->delete();

        return $this->messageResponse("Categoría «{$nombre}» eliminada correctamente.");
    }
}
