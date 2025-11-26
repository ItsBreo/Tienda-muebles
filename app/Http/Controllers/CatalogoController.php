<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Models\Furniture;
use App\Models\Category;

// Controlador de Catálogo
class CatalogoController extends Controller
{

    // Listado de categorías
    public function categorias(Request $request){
        // La vista puede acceder a la sesión directamente con auth()->check() y auth()->user()
        $preferencias = []; // Puedes añadir la lógica de cookies si es necesario para esta vista

        $categories = Category::all();

        return view('catalogo.categorias', compact('categories', 'preferencias'));
    }

    // Mostrar muebles por categoría
    public function show(Request $request, $id){
        $category = Category::find((int)$id);

        // Si la categoría no existe, redirigir
        if (!$category) {
            return redirect()->route('principal');
        }


        Cookie::queue("categoria_{$category->id}", json_encode($category), 60 * 24 * 30);

        return redirect()->route('muebles.index', [
            'category' => $category->id,
        ]);
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

        // Iniciamos la consulta de muebles con Eloquent
        $query = Furniture::query();

        // Filtrar por categoría
        if ($request->filled('category')) {
            $catId = (int)$request->input('category');
            $query->where('category_id', $catId);
        }

        // Filtrar rango precio: min_price, max_price
        if ($request->filled('min_price')) {
            $min = (float)$request->input('min_price');
            $query->where('price', '>=', $min);
        }
        if ($request->filled('max_price')) {
            $max = (float)$request->input('max_price');
            $query->where('price', '<=', $max);
        }

        // Filtrar por color
        if ($request->filled('color')) {
            $color = strtolower($request->input('color'));
            $query->whereRaw('LOWER(main_color) LIKE ?', ["%{$color}%"]);
        }

        // Filtrar por búsqueda general (nombre y descripción)
        if ($request->filled('q')) {
            $searchTerm = strtolower($request->input('q'));
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // Ordenamos price_asc, price_desc, name_asc, name_desc, new
        switch ($request->input('sort')) {
            case 'price_asc':  $query->orderBy('price', 'asc'); break;
            case 'price_desc': $query->orderBy('price', 'desc'); break;
            case 'name_asc':   $query->orderBy('name', 'asc'); break;
            case 'name_desc':  $query->orderBy('name', 'desc'); break;
            case 'new':
            default:           $query->orderBy('id', 'desc'); break;
        }

        // Paginar los resultados
        $paginator = $query->paginate($perPage);

        // Añadir los parámetros de la query string a los enlaces de paginación
        $paginator->appends($request->except('page'));


        // Pasamos categorías para filtros
        $categories = Category::all();

        // Devolver la vista y pasar las variables
        return view('catalogo.index', [
            'muebles' => $paginator,
            'categories' => $categories,
            'preferencias' => $preferencias,
        ]);
    }
}
