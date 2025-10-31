<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cookie;
use App\Models\Furniture;
use App\Models\Category;

// Controlador de Catálogo
class CatalogoController extends Controller
{
    // Listado de categorías
    public function categorias(){
        $categories = Category::getMockData();
        return view('catalogo.categorias', compact('categories'));
    }

    // Mostrar muebles por categoría
    public function show(Request $request, $id){
        $category = Category::findById((int)$id);

        // Guardar cookie por categoría con función queque (nombre: categoria_{id})
        Cookie::queue("categoria_{$category->getId()}", json_encode($category), 60 * 24 * 30);

        // Redirigimos a /muebles con parámetro category para el filtrado
        return redirect()->route('muebles.index', ['category' => $category->getId()]);
    }

    // Listado de muebles con filtros, orden y paginación
    public function index(Request $request)
    {
        // Obtenemos preferencia de paginación desde cookie (nombre: pref_pagination)
        $perPage = (int) $request->cookie('pref_pagination', 6);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Obtenemos todos los muebles
        $items = collect(Furniture::getMockData());

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

        // Texto (nombre o descripción)
        if ($request->filled('query')) {
            $query = strtolower($request->input('query'));
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

        // Paginar manualmente (Tengo dudas)
        $total = $items->count();
        $slice = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginator = new LengthAwarePaginator($slice, $total, $perPage, $currentPage, [
            'path' => url()->current(),
            'query' => $request->query(),
        ]);

        // Pasamos categorías para filtros
        $categories = Category::getMockData();

        // Vista con muebles paginados y filtros
        return view('catalogo.index', [
            'muebles' => $paginator,
            'categories' => $categories,
            'perPage' => $perPage
        ]);
    }
}
