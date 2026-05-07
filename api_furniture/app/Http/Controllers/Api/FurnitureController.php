<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FurnitureListResource;
use App\Http\Resources\FurnitureResource;
use App\Models\Furniture;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FurnitureController extends Controller
{
    use ApiResponse;

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/furniture
    // Listado paginado con filtros y ordenación.
    //
    // Parámetros de filtro (todos opcionales):
    //   ?category=1           → filtra por category_id
    //   ?q=silla              → búsqueda en nombre y descripción
    //   ?min_price=50         → precio mínimo
    //   ?max_price=500        → precio máximo
    //   ?color=Blanco         → filtra por color exacto
    //   ?only_salient=true    → solo muebles destacados
    //
    // Parámetros de ordenación:
    //   ?sort=price_asc       → precio ascendente
    //   ?sort=price_desc      → precio descendente
    //   ?sort=name_asc        → nombre A→Z
    //   ?sort=name_desc       → nombre Z→A
    //   ?sort=date_new        → más recientes primero
    //   ?sort=date_old        → más antiguos primero
    //   (por defecto: más recientes primero)
    //
    // Paginación:
    //   ?per_page=12          → resultados por página (6|12|24|48)
    //   ?page=2               → número de página
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        // ── Validación de parámetros de entrada ───────────────────────────────
        $validated = $request->validate([
            'category'      => 'nullable|integer|exists:categories,id',
            'q'             => 'nullable|string|max:100',
            'min_price'     => 'nullable|numeric|min:0',
            'max_price'     => 'nullable|numeric|min:0|gte:min_price',
            'color'         => 'nullable|string|max:100',
            'only_salient'  => 'nullable|boolean',
            'sort'          => 'nullable|string|in:price_asc,price_desc,name_asc,name_desc,date_new,date_old',
            'per_page'      => 'nullable|integer|in:6,12,24,48',
        ]);

        // ── Construcción de la query ───────────────────────────────────────────
        $query = Furniture::with(['images', 'category']);

        // Filtro por categoría
        if (!empty($validated['category'])) {
            $query->where('category_id', $validated['category']);
        }

        // Búsqueda de texto libre en nombre y descripción
        if (!empty($validated['q'])) {
            $term = trim($validated['q']);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }

        // Rango de precios
        if (isset($validated['min_price'])) {
            $query->where('price', '>=', (float) $validated['min_price']);
        }
        if (isset($validated['max_price'])) {
            $query->where('price', '<=', (float) $validated['max_price']);
        }

        // Filtro por color
        if (!empty($validated['color'])) {
            $query->where('main_color', $validated['color']);
        }

        // Solo destacados
        if (filter_var($validated['only_salient'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->where('is_salient', true);
        }

        // ── Ordenación ────────────────────────────────────────────────────────
        match ($validated['sort'] ?? 'date_new') {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            'date_old'   => $query->orderBy('created_at', 'asc'),
            default      => $query->orderBy('created_at', 'desc'), // date_new
        };

        // ── Paginación ────────────────────────────────────────────────────────
        $perPage = (int) ($validated['per_page'] ?? 12);
        $muebles = $query->paginate($perPage)->withQueryString();

        // ── Respuesta con filtros activos incluidos ────────────────────────────
        return $this->paginatedResponse($muebles, FurnitureListResource::class, [
            'category'     => $validated['category'] ?? null,
            'q'            => $validated['q'] ?? null,
            'min_price'    => $validated['min_price'] ?? null,
            'max_price'    => $validated['max_price'] ?? null,
            'color'        => $validated['color'] ?? null,
            'only_salient' => isset($validated['only_salient'])
                                  ? filter_var($validated['only_salient'], FILTER_VALIDATE_BOOLEAN)
                                  : null,
            'sort'         => $validated['sort'] ?? 'date_new',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/furniture/featured
    // Muebles destacados (is_salient = true).
    // ?limit=6  → máximo de resultados (1-24, por defecto 6)
    // ──────────────────────────────────────────────────────────────────────────
    public function featured(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:24',
        ]);

        $limit   = (int) ($validated['limit'] ?? 6);
        $muebles = Furniture::with(['images', 'category'])
            ->where('is_salient', true)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        return $this->okResponse(
            FurnitureListResource::collection($muebles)->resolve(request()),
            'Muebles destacados.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/furniture/colors
    // Lista de colores únicos disponibles (para los filtros del catálogo).
    // ──────────────────────────────────────────────────────────────────────────
    public function colors(): JsonResponse
    {
        $colors = Furniture::select('main_color')
            ->whereNotNull('main_color')
            ->where('main_color', '!=', '')
            ->distinct()
            ->orderBy('main_color')
            ->pluck('main_color');

        return $this->okResponse($colors, 'Colores disponibles.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/furniture/{id}
    // Detalle completo: todas las imágenes (principal primero) y categoría.
    // ──────────────────────────────────────────────────────────────────────────
    public function show(Furniture $furniture): JsonResponse
    {
        $furniture->load([
            'images'   => fn($q) => $q->orderByDesc('is_primary')->orderBy('display_order'),
            'category',
        ]);

        return $this->okResponse(
            (new FurnitureResource($furniture))->resolve(request()),
            'Detalle del mueble.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/furniture
    // Crea un nuevo mueble. Requiere token de admin.
    // ──────────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price'       => 'required|numeric|min:0|max:999999.99',
            'stock'       => 'required|integer|min:0|max:9999',
            'category_id' => 'required|integer|exists:categories,id',
            'materials'   => 'nullable|string|max:255',
            'dimensions'  => 'nullable|string|max:100',
            'main_color'  => 'required|string|max:100',
            'is_salient'  => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $validated['is_salient'] = $request->boolean('is_salient');

        $mueble = Furniture::create($validated);

        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $mueble->images()->create([
                'image_path' => 'images/' . $imageName,
                'is_primary' => true,
                'alt_text'   => $mueble->name,
            ]);
        }

        $mueble->load(['images', 'category']);

        return $this->createdResponse(
            (new FurnitureResource($mueble))->resolve(request()),
            'Mueble creado correctamente.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PUT /api/furniture/{id}
    // Actualiza un mueble existente. Requiere token de admin.
    // ──────────────────────────────────────────────────────────────────────────
    public function update(Request $request, Furniture $mueble): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'price'       => 'required|numeric|min:0|max:999999.99',
            'stock'       => 'required|integer|min:0|max:9999',
            'category_id' => 'required|integer|exists:categories,id',
            'materials'   => 'nullable|string|max:255',
            'dimensions'  => 'nullable|string|max:100',
            'main_color'  => 'required|string|max:100',
            'is_salient'  => 'nullable|boolean',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        $validated['is_salient'] = $request->boolean('is_salient');

        $mueble->update($validated);

        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            // Quitar principal anterior y añadir la nueva
            $mueble->images()->update(['is_primary' => false]);
            $mueble->images()->create([
                'image_path' => 'images/' . $imageName,
                'is_primary' => true,
                'alt_text'   => $mueble->name,
            ]);
        }

        $mueble->load([
            'images'   => fn($q) => $q->orderByDesc('is_primary')->orderBy('display_order'),
            'category',
        ]);

        return $this->okResponse(
            (new FurnitureResource($mueble))->resolve(request()),
            'Mueble actualizado correctamente.'
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/furniture/{id}
    // Elimina un mueble. Requiere token de admin.
    // ──────────────────────────────────────────────────────────────────────────
    public function destroy(Furniture $mueble): JsonResponse
    {
        $mueble->delete();

        return $this->messageResponse('Mueble eliminado correctamente.');
    }
}
