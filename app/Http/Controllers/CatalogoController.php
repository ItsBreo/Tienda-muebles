<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session; // <-- Importar Session
use App\Models\Furniture;
use App\Models\Category;

// Controlador de Catálogo
class CatalogoController extends Controller
{

    // Listado de categorías
    public function categorias(Request $request){
        // La vista puede acceder a la sesión directamente con auth()->check() y auth()->user()
        $preferencias = []; // Puedes añadir la lógica de cookies si es necesario para esta vista
        $activeSesionId = null; // Ya no se usa

        $categories = Category::getMockData();

        return view('catalogo.categorias', compact('categories', 'activeSesionId', 'preferencias'));
    }

    // Mostrar muebles por categoría
    public function show(Request $request, $id){
        $category = Category::findById((int)$id);

        // Si la categoría no existe, redirigir
        if (!$category) {
            return redirect()->route('principal');
        }


        Cookie::queue("categoria_{$category->getId()}", json_encode($category), 60 * 24 * 30);

        return redirect()->route('muebles.index', [
            'category' => $category->getId(),
        ]);
    }

    private $sessionKey = 'muebles_crud_session';

    private function getMuebles()
    {
        // Intenta obtener los muebles de la sesión
        $muebles = Session::get($this->sessionKey);

        // Si la sesión tiene una colección de muebles, la devuelve
        if ($muebles instanceof \Illuminate\Support\Collection) {
            return $muebles;
        }

        // Si no, carga los datos mock
        $muebles = collect(Furniture::getMockData());

        // Guarda los muebles en la sesión para futuros usos
        Session::put($this->sessionKey, $muebles);

        return $muebles;
    }
    // Listado de muebles con filtros, orden y paginación
    public function index(Request $request)
    {
        // Valores por defecto para las preferencias
        $preferencias = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        // Si el usuario está autenticado, intentamos leer su cookie de preferencias.
        if (auth()->check()) {
            $cookieName = 'preferencias_' . auth()->user()->id;
            if ($request->hasCookie($cookieName)) {
                $preferencias = array_merge($preferencias, json_decode($request->cookie($cookieName), true));
            }
        }

        // Obtenemos preferencia de paginación desde NUESTRA cookie
        $perPage = max((int) ($preferencias['tamaño'] ?? 6), 6); // Mínimo 6
        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');

        // Obtenemos todos los muebles
        $items = $this->getMuebles();

        // Filtrar por categoría
        if ($request->filled('category')) {
            $catId = (int)$request->input('category');
            $items = $items->filter(fn($mueble) => $mueble->getCategoryId() === $catId);
        }

        // Filtrar rango precio: min_price, max_price
        if ($request->filled('min_price')) {
            $min = (float)$request->input('min_price');
            $items = $items->filter(fn($mueble) => $mueble->getPrice() >= $min);
        }
        if ($request->filled('max_price')) {
            $max = (float)$request->input('max_price');
            $items = $items->filter(fn($mueble) => $mueble->getPrice() <= $max);
        }

        // Filtrar por color
        if ($request->filled('color')) {
            $color = strtolower($request->input('color'));
            $items = $items->filter(fn($mueble) => str_contains(strtolower($mueble->getMainColor()), $color));
        }

        // Filtrar por búsqueda general (nombre y descripción)
        if ($request->filled('q')) {
            $query = strtolower($request->input('q'));
            $items = $items->filter(fn($mueble) =>
                str_contains(strtolower($mueble->getName()), $query) || str_contains(strtolower($mueble->getDescription()), $query)
            );
        }

        // Ordenamos price_asc, price_desc, name_asc, name_desc, new
        if ($request->filled('sort')) {
            switch ($request->input('sort')) {
                case 'price_asc':
                    $items = $items->sortBy(fn($m) => $m->getPrice());
                    break;
                case 'price_desc':
                    $items = $items->sortByDesc(fn($m) => $m->getPrice());
                    break;
                case 'name_asc':
                    $items = $items->sortBy(fn($m) => $m->getName());
                    break;
                case 'name_desc':
                    $items = $items->sortByDesc(fn($m) => $m->getName());
                    break;
                case 'new':
                default:
                    $items = $items->sortByDesc(fn($m) => $m->getId());
                    break;
            }
        }

        // Reindexar colección después de filtros y orden
        $items = $items->values();

        // Paginar manualmente
        $total = $items->count();
        $slice = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();

        // Pasar los query params al paginador para que los filtros persistan
        $paginator = new LengthAwarePaginator($slice, $total, $perPage, $currentPage, [
            'path' => Paginator::resolveCurrentPath(), // Usa Paginator
            'pageName' => 'page',
        ]);

        $paginator->appends($request->except('page'));


        // Pasamos categorías para filtros
        $categories = Category::getMockData();

        // Devolver la vista y pasar las variables
        return view('catalogo.index', [
            'muebles' => $paginator,
            'categories' => $categories,
            'preferencias' => $preferencias,
        ]);
    }
}
