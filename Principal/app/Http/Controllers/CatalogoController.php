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
        // Obtener ID de sesión (URL o Input)
        $activeSesionId = $request->input('sesionId') ?? $request->query('sesionId');

        $activeUser = null;

        // Valores por defecto de preferencias
        $preferencias = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        // Buscamos el usuario por su id de sesión
        if ($activeSesionId) {
            $activeUser = User::activeUserSesion($activeSesionId);
        }

        // Si hay usuario activo, leemos su cookie personalizada
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
        // Devolvemos los datos
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
        // Cargar Sessón y preferencias
        $sesionData = $this->getSesionYPreferencias($request);
        $preferencias = $sesionData['preferencias'];
        $perPage = (int) ($preferencias['tamaño'] ?? 6);
        if (!in_array($perPage, [6, 12, 24])) $perPage = 6;

        // Creamos la consulta de la BD para el catálogo
        $query = Furniture::query();

        // Filtros del catálogo
        // Categoría
        if ($request->filled('category')) {
            // Consulta WHERE en la BD para la categoría
            $query->where('category_id', $request->category);
        }

        // Búsqueda
        if ($request->filled('q')) {
            // Buscamos en el nombre y la descripción del mueble en la BD
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }

        // Precio Mínimo
        if ($request->filled('min_price')) {
            // Forzamos a que sea número para evitar errores de string vacíos raros
            $min = (float) $request->min_price;
            // Consulta WHERE en la BD para el precio mínimo
            $query->where('price', '>=', $min);
        }

        // Precio Máximo
        if ($request->filled('max_price')) {
            $max = (float) $request->max_price;
            $query->where('price', '<=', $max);
        }

        // Color seleccionado
        if ($request->filled('color')) {
            $query->where('main_color', 'LIKE', $request->color);
        }

        // Ordenación
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

        // Paginación
        $muebles = $query->paginate($perPage)->withQueryString();

        // Datos Auxiliares
        $categories = Category::all();

        // Obtenemos los colores de los muebles con DB
        $colors = Furniture::query()
                    ->select('main_color')
                    ->whereNotNull('main_color')
                    ->distinct()
                    ->orderBy('main_color')
                    ->pluck('main_color');
        // Pasamos todo a la vista
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

    // Muestra el detalle de un mueble
    public function showMueble(Request $request, $id){
        // Cargamos la sesión y las preferencias (Helper existente)
        $sesionData = $this->getSesionYPreferencias($request);

        // Buscamos el mueble
        $mueble = Furniture::find($id);

        if (!$mueble) {
            abort(404, 'Mueble no encontrado');
        }

        // Control de stock
        $activeSesionId = $sesionData['activeSesionId'];
        $stockTotal = $mueble->stock;
        $enCarrito = 0;

        // Verificamos si hay una sesión activa
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


        // Creamos cookie de historial
        Cookie::queue("mueble_{$mueble->id}", json_encode($mueble), 60 * 24 * 30);

        // Enviamos los datos necesarios a la vista
        return view('muebles.show', array_merge($sesionData, [
            'mueble' => $mueble,
            'stockTotal' => $stockTotal,
            'enCarrito' => $enCarrito,
            'stockDisponible' => $stockDisponible
        ]));
    }
}
