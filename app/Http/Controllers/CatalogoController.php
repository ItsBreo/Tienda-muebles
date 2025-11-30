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
    // 1. Preferencias (Igual que tenías)
    $sesionData = $this->getSesionYPreferencias($request);
    $preferencias = $sesionData['preferencias'];
    $perPage = (int) ($preferencias['tamaño'] ?? 6);
    if (!in_array($perPage, [6, 12, 24])) $perPage = 6;

    // 2. Query Builder
    $query = Furniture::query();

    // --- DEPURACIÓN RÁPIDA (Descomenta si falla para ver qué llega) ---
    // dd($request->all());
    // ------------------------------------------------------------------

    // 3. Filtros

    // Categoría (Dices que este funciona, así que 'category_id' es correcto)
    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    // Búsqueda (q)
    if ($request->filled('q')) {
        $term = $request->q;
        $query->where(function ($q) use ($term) {
            // ASEGÚRATE: ¿Tus columnas en la BD se llaman 'name' y 'description'?
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }

    // Precio Mínimo
    if ($request->filled('min_price')) {
        // Forzamos a que sea número para evitar errores de string vacíos raros
        $min = (float) $request->min_price;
        // ASEGÚRATE: ¿La columna en la BD se llama 'price'?
        $query->where('price', '>=', $min);
    }

    // Precio Máximo
    if ($request->filled('max_price')) {
        $max = (float) $request->max_price;
        $query->where('price', '<=', $max);
    }

    // Color
    if ($request->filled('color')) {
        // ASEGÚRATE: ¿La columna en la BD se llama 'main_color'?
        // Si en la BD se llama 'color' o 'color_id', cámbialo aquí.
        $query->where('main_color', 'LIKE', $request->color);
    }

    // 4. Ordenación
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

    // 5. Paginación
    $muebles = $query->paginate($perPage)->withQueryString();

    // 6. Datos Auxiliares
    $categories = Category::all();

    // IMPORTANTE: Para los colores, sacamos solo los que existen REALMENTE en la columna 'main_color'
    $colors = Furniture::query()
                ->select('main_color')
                ->whereNotNull('main_color')
                ->distinct()
                ->orderBy('main_color')
                ->pluck('main_color');

    return view('catalogo.index', array_merge($sesionData, [
        'muebles'    => $muebles,
        'categories' => $categories,
        'colors'     => $colors
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
        // 1. Cargamos la sesión y las preferencias (Helper existente)
        $sesionData = $this->getSesionYPreferencias($request);

        // 2. Buscamos el mueble
        $mueble = Furniture::find($id);

        if (!$mueble) {
            abort(404, 'Mueble no encontrado');
        }

        // 3. LÓGICA DE STOCK (Movida desde la Vista al Controlador)
        $activeSesionId = $sesionData['activeSesionId'];
        $stockTotal = $mueble->stock;
        $enCarrito = 0;

        if ($activeSesionId) {
            // Usamos el helper del modelo User para buscar al usuario de esta sesión
            $u = User::activeUserSesion($activeSesionId);

            if ($u) {
                // Obtenemos el carrito de la sesión
                $carritoSesion = Session::get('carrito_' . $u->id, []);

                // Verificamos si este mueble específico está en el carrito
                if (isset($carritoSesion[$mueble->id])) {
                    $enCarrito = (int) $carritoSesion[$mueble->id]['cantidad'];
                }
            }
        }

        // Calculamos lo que queda realmente disponible para comprar
        $stockDisponible = max(0, $stockTotal - $enCarrito);


        // 4. Creamos cookie de historial (mantenemos tu lógica existente)
        Cookie::queue("mueble_{$mueble->id}", json_encode($mueble), 60 * 24 * 30);

        // 5. Enviamos TODOS los datos calculados a la vista
        return view('muebles.show', array_merge($sesionData, [
            'mueble' => $mueble,
            'stockTotal' => $stockTotal,       // Total en BD
            'enCarrito' => $enCarrito,         // Lo que ya tiene el user
            'stockDisponible' => $stockDisponible // Lo que puede comprar ahora
        ]));
    }
}
