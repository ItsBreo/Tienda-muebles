<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * Devuelve todas las categorías.
     */
    public function index()
    {
        return response()->json(Category::all(), 200);
    }

    /**
     * POST /api/categories
     * Crea una nueva categoría.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $categoria = Category::create($data);

        return response()->json([
            'message'   => 'Categoría creada correctamente.',
            'categoria' => $categoria,
        ], 201);
    }

    /**
     * GET /api/categories/{id}
     * Muestra una categoría específica.
     */
    public function show(Category $categoria)
    {
        return response()->json($categoria->load('furniture'), 200);
    }

    /**
     * PUT /api/categories/{id}
     * Actualiza una categoría.
     */
    public function update(Request $request, Category $categoria)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,' . $categoria->id,
            'description' => 'nullable|string',
        ]);

        $categoria->update($data);

        return response()->json([
            'message'   => 'Categoría actualizada correctamente.',
            'categoria' => $categoria->fresh(),
        ], 200);
    }

    /**
     * DELETE /api/categories/{id}
     * Elimina una categoría.
     */
    public function destroy(Category $categoria)
    {
        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente.'], 200);
    }
}
