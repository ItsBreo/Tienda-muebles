<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use App\Models\Furniture;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

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

        // Cargamos las categorías con DB
        $categories = Category::all();

        // Pasamos todo a la vista
        return view('catalogo.categorias', array_merge($data, [
            'categories' => $categories
        ]));
    }

    /**
     * Función para mostrar el catálogo principal (index) con filtros y paginación.
     */
    public function index(Request $request)
    {
        // Cargamos las preferencias
        $sesionData = $this->getSesionYPreferencias($request);
        $preferencias = $sesionData['preferencias'];

        // Cargamos datos de nuestra DB
        $categories = Category::all();
        $items = collect(Furniture::all());

        // Recogemos los colores de los muebles
        $colors = $items->pluck('main_color')->unique()->sort();

        // Lógica de Filtrado
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
        if ($request->filled('color')) {
            $items = $items->filter(fn($m) => $m->main_color == $request->color);
        }

        // 4. Lógica de Ordenación
        $sort = $request->input('sort', 'default');
        $items = match ($sort) {
            'price_asc' => $items->sortBy(fn($m) => $m->price),
            'price_desc' => $items->sortByDesc(fn($m) => $m->price),
            'name_asc' => $items->sortBy(fn($m) => $m->name),
            'name_desc' => $items->sortByDesc(fn($m) => $m->name),
            'date_new' => $items->sortByDesc(fn($m) => $m->created_at ?? 0),
            'date_old' => $items->sortBy(fn($m) => $m->created_at ?? 0),
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
            'colors' => $colors,
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

    public function showMueble(Request $request, $id){
        // Cargamos la sesión y las preferencias
        $sesionData = $this->getSesionYPreferencias($request);

        // Buscamos el mueble
        $mueble = Furniture::find($id);

        if (!$mueble) {
            abort(404, 'Mueble no encontrado');
        }

        // Creamos cookie para el mueble mostrado (mueble_{id}) por 30 días
        Cookie::queue("mueble_{$mueble->id}", json_encode($mueble), 60 * 24 * 30);

        return view('muebles.show', array_merge($sesionData, [
            'mueble' => $mueble,
        ]));
    }
}
