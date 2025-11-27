<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use App\Models\Furniture;
use App\Models\Category;
use App\Models\User;

class CatalogoController extends Controller
{
    /**
     * Helper privado para cargar sesión y preferencias de forma consistente.
     */
    private function getSesionYPreferencias(Request $request)
    {
        // 1. Obtener ID de sesión (URL o Input)
        $activeSesionId = $request->input('sesionId') ?? $request->query('sesionId');

        $activeUser = null;

        // Valores por defecto
        $preferencias = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        // 2. Intentar recuperar el usuario
        if ($activeSesionId) {
            $activeUser = User::activeUserSesion($activeSesionId);
        }

        // 3. Si hay usuario, leer su cookie personalizada
        if ($activeUser) {
            $cookieName = 'preferencias_' . $activeUser->id;

            // Laravel desencripta la cookie automáticamente
            $cookieValue = $request->cookie($cookieName);

            // Decodificamos el JSON si existe
            if ($cookieValue) {
                $cookieData = json_decode($cookieValue, true);
                if (is_array($cookieData)) {
                    $preferencias = array_merge($preferencias, $cookieData);
                }
            }
        }

        return compact('activeSesionId', 'activeUser', 'preferencias');
    }

    /**
     * Muestra la lista de categorías.
     */
    public function categorias(Request $request) {
        // Cargar datos comunes
        $data = $this->getSesionYPreferencias($request);

        // Cargar categorías (Mock o BD)
        // !! CORRECCIÓN: Usamos el método correcto si ya has migrado a BD, o getMockData si sigues con mocks !!
        // Si ya tienes el modelo Category con BD, usa Category::all();
        // Si sigues con el mock, usa Category::getMockData();
        // Como estamos en transición, usaré una comprobación segura o el mock por defecto que tenías.
        if (method_exists(Category::class, 'getMockData')) {
             $categories = Category::getMockData();
        } else {
             $categories = Category::all();
        }

        // Pasamos todo a la vista
        return view('catalogo.categorias', array_merge($data, [
            'categories' => $categories
        ]));
    }

    /**
     * Muestra el catálogo principal (index) con filtros y paginación.
     */
    public function index(Request $request)
    {
        // 1. Cargar preferencias
        $sesionData = $this->getSesionYPreferencias($request);
        $preferencias = $sesionData['preferencias'];

        // 2. Cargar datos base
        if (method_exists(Category::class, 'getMockData')) {
             $categories = Category::getMockData();
             $items = collect(Furniture::getMockData());
        } else {
             $categories = Category::all();
             // Si ya migraste Furniture a Eloquent, usa Furniture::all();
             // Si no, mantén el mock.
             $items = collect(Furniture::all());
        }

        // 3. Lógica de Filtrado
        if ($request->filled('category')) {
            $items = $items->filter(fn($m) => $m->category_id == $request->category);
        }
        if ($request->filled('q')) {
            $q = strtolower($request->q);
            $items = $items->filter(fn($m) =>
                str_contains(strtolower($m->name), $q) ||
                str_contains(strtolower($m->description), $q)
            );
        }
        if ($request->filled('min_price')) {
            $items = $items->filter(fn($m) => $m->price >= $request->min_price);
        }
        if ($request->filled('max_price')) {
            $items = $items->filter(fn($m) => $m->price <= $request->max_price);
        }

        // 4. Lógica de Ordenación
        $sort = $request->input('sort', 'default');
        $items = match ($sort) {
            'price_asc' => $items->sortBy(fn($m) => $m->price),
            'price_desc' => $items->sortByDesc(fn($m) => $m->price),
            'name_asc' => $items->sortBy(fn($m) => $m->name),
            'name_desc' => $items->sortByDesc(fn($m) => $m->name),
            default => $items,
        };

        // 5. Paginación usando la preferencia
        // Aseguramos que $perPage sea un entero válido
        $perPage = (int) ($preferencias['tamaño'] ?? 6);
        if ($perPage < 1) $perPage = 6;

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator($currentItems, $items->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        // 6. Pasar todas las variables a la vista
        return view('catalogo.index', array_merge($sesionData, [
            'muebles' => $paginator,
            'categories' => $categories,
        ]));
    }

    /**
     * Redirige al catálogo filtrado por una categoría.
     */
    public function show(Request $request, $id){
        $sesionData = $this->getSesionYPreferencias($request);

        // Intenta encontrar la categoría (Mock o BD)
        if (method_exists(Category::class, 'findById')) {
            $category = Category::findById((int)$id);
        } else {
            $category = Category::find($id);
        }

        if (!$category) {
            return redirect()->route('principal', ['sesionId' => $sesionData['activeSesionId']]);
        }

        // Usa el getter getId() si existe, o la propiedad id directamente
        $catId = method_exists($category, 'getId') ? $category->getId() : $category->id;

        return redirect()->route('muebles.index', [
            'category' => $catId,
            'sesionId' => $sesionData['activeSesionId']
        ]);
    }
}
