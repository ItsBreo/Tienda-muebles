<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class FurnitureController extends Controller
{
    /**
     * GET /api/furniture
     * Listado público con filtros opcionales: category, q, min_price, max_price, color, sort, per_page.
     */
    public function index(Request $request)
    {
        $query = Furniture::with('images', 'category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->max_price);
        }

        if ($request->filled('color')) {
            $query->where('main_color', 'LIKE', $request->color);
        }

        $sort = $request->input('sort', 'default');
        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            'date_new'   => $query->orderBy('created_at', 'desc'),
            'date_old'   => $query->orderBy('created_at', 'asc'),
            default      => $query->orderBy('id', 'desc'),
        };

        $perPage = (int) $request->input('per_page', 12);
        if (!in_array($perPage, [6, 12, 24, 48, 100])) {
            $perPage = 12;
        }

        $muebles = $query->paginate($perPage)->withQueryString();

        return response()->json($muebles, 200);
    }

    /**
     * GET /api/furniture/featured
     * Muebles destacados (is_salient = true).
     */
    public function featured(Request $request)
    {
        $limit = (int) $request->input('limit', 6);
        $muebles = Furniture::with('images')->where('is_salient', true)->take($limit)->get();

        return response()->json($muebles, 200);
    }

    /**
     * GET /api/furniture/colors
     * Lista de colores únicos disponibles.
     */
    public function colors()
    {
        $colors = Furniture::select('main_color')
            ->whereNotNull('main_color')
            ->distinct()
            ->orderBy('main_color')
            ->pluck('main_color');

        return response()->json($colors, 200);
    }

    /**
     * POST /api/furniture
     * Crea un nuevo mueble.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'materials'   => 'nullable|string',
            'dimensions'  => 'nullable|string',
            'main_color'  => 'required|string|max:100',
            'is_salient'  => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['is_salient'] = $request->boolean('is_salient');

        $mueble = Furniture::create($data);

        // Imagen principal
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $mueble->images()->create([
                'image_path' => 'images/' . $imageName,
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => 'Mueble creado correctamente.',
            'mueble'  => $mueble->load('images', 'category'),
        ], 201);
    }

    /**
     * GET /api/furniture/{id}
     * Detalle de un mueble.
     */
    public function show(Furniture $mueble)
    {
        return response()->json($mueble->load('images', 'category'), 200);
    }

    /**
     * PUT /api/furniture/{id}
     * Actualiza un mueble existente.
     */
    public function update(Request $request, Furniture $mueble)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'materials'   => 'nullable|string',
            'dimensions'  => 'nullable|string',
            'main_color'  => 'required|string|max:100',
            'is_salient'  => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data['is_salient'] = $request->boolean('is_salient');

        $mueble->update($data);

        // Imagen nueva opcional
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $mueble->images()->update(['is_primary' => false]);
            $mueble->images()->create([
                'image_path' => 'images/' . $imageName,
                'is_primary' => true,
            ]);
        }

        return response()->json([
            'message' => 'Mueble actualizado correctamente.',
            'mueble'  => $mueble->fresh()->load('images', 'category'),
        ], 200);
    }

    /**
     * DELETE /api/furniture/{id}
     * Elimina un mueble.
     */
    public function destroy(Furniture $mueble)
    {
        $mueble->delete();

        return response()->json(['message' => 'Mueble eliminado correctamente.'], 200);
    }
}
