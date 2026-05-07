<?php

namespace App\Http\Controllers;

use App\Services\ApiFurnitureService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Session;

class CatalogoController extends Controller
{
    protected ApiFurnitureService $apiFurniture;

    public function __construct(ApiFurnitureService $apiFurniture)
    {
        $this->apiFurniture = $apiFurniture;
    }

    public function index(Request $request)
    {
        $response = $this->apiFurniture->getFurnitureList($request->all());
        
        $mueblesData = $response['data'] ?? [];
        $pagination = $response['pagination'] ?? [];

        $muebles = new LengthAwarePaginator(
            $mueblesData,
            $pagination['total'] ?? count($mueblesData),
            $pagination['per_page'] ?? 12,
            $pagination['current_page'] ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categories = $this->apiFurniture->getCategories();
        
        // Extraer colores únicos desde la API (asumiendo que hay un endpoint o los extraemos del catálogo)
        // Como no tenemos el endpoint getColors() en ApiFurnitureService, vamos a agregarlo en un momento
        // o por ahora lo dejamos vacío.
        $colors = $this->apiFurniture->getColors();

        $activeSesionId = $request->query('sesionId');

        return view('catalogo.index', compact('muebles', 'categories', 'colors', 'activeSesionId'));
    }

    public function showMueble($id, Request $request)
    {
        $mueble = $this->apiFurniture->getFurnitureDetail($id);

        if (!$mueble) {
            abort(404);
        }

        $activeSesionId = $request->query('sesionId');
        $user = Session::get('user');

        $enCarrito = 0;
        if ($user) {
            $cart = Session::get('carrito_' . $user['id'], []);
            $enCarrito = isset($cart[$id]) ? (int)$cart[$id]['cantidad'] : 0;
        }

        $stockTotal = $mueble['stock'] ?? 0;
        $stockDisponible = max(0, $stockTotal - $enCarrito);

        return view('muebles.show', compact('mueble', 'activeSesionId', 'stockTotal', 'enCarrito', 'stockDisponible'));
    }

    public function indexCategorias()
    {
        $categorias = $this->apiFurniture->getCategories();
        return view('catalogo.categorias', compact('categorias'));
    }

    public function showCategoria($id, Request $request)
    {
        $response = $this->apiFurniture->getFurnitureList(['category' => $id]);
        
        $mueblesData = $response['data'] ?? [];
        $pagination = $response['pagination'] ?? [];

        $muebles = new LengthAwarePaginator(
            $mueblesData,
            $pagination['total'] ?? count($mueblesData),
            $pagination['per_page'] ?? 12,
            $pagination['current_page'] ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $categorias = $this->apiFurniture->getCategories();
        $categoria = collect($categorias)->firstWhere('id', (int) $id);

        $activeSesionId = $request->query('sesionId');

        return view('catalogo.show', compact('muebles', 'categoria', 'activeSesionId'));
    }
}
