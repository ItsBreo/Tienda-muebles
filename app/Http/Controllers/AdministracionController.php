<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
// !! AÑADIMOS LAS CLASES PARA LA SESIÓN Y EL MODELO USER !!
use Illuminate\Support\Facades\Session;
use App\Models\User;

class AdministracionController extends Controller
{
    private $cookieName = 'muebles_crud';

    private $cookieMinutes = 60 * 24 * 7; // 1 semana

    private function getMuebles()
    {
        // 1. Intenta obtener el JSON de la cookie
        $mueblesJson = Cookie::get($this->cookieName);

        if ($mueblesJson) {
            // 2. Decodifica el JSON a un array de arrays
            $mueblesArrays = json_decode($mueblesJson, true);

            // SI EXISTEN, paasmos los arrays a Objetos Furniture
            return collect($mueblesArrays)->map(function ($item) {
                return new Furniture(
                    $item['id'],
                    $item['category_id'],
                    $item['name'],
                    $item['description'],
                    $item['price'],
                    $item['stock'],
                    $item['materials'] ?? '',
                    $item['dimensions'] ?? '',
                    $item['main_color'],
                    $item['is_salient'],
                    $item['images']
                );
            });
        }

        // 4. Si no, carga los datos mock (que ya son Objetos Furniture)
        $muebles = collect(Furniture::getMockData());

        // 5. Los guarda en la cookie para la próxima vez
        $this->saveMuebles($muebles);
        return $muebles;
    }

    /**
     * Guarda la colección de muebles en la cookie.
     */
    private function saveMuebles($muebles)
    {
        // Convertimos la colección de Objetos a JSON y la ponemos en la cola
        // para que se guarde en la respuesta del navegador.
        Cookie::queue($this->cookieName, $muebles->toJson(), $this->cookieMinutes);
    }


    /**
     * Revisa si el usuario actual es un admin.
     * Devuelve 'true' si es admin, o una Redirección si no lo es.
     */
    private function checkAdmin(Request $request)
    {
        // Obtenemos el sesionId de la URL (o del formulario)
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');

        if (!$sesionId) {
            // Si no hay sesionId, no está autenticado.
            return redirect()->route('login.show')->with('error', 'Debes iniciar sesión.');
        }

        // 2. Buscamos al usuario en el array de la sesión
        $usuarios = Session::get('usuarios', []);
        $userJson = $usuarios[$sesionId] ?? null;

        if (!$userJson) {
            // Si el sesionId no está en la sesión, se ha invalidado.
            return redirect()->route('login.show')->with('error', 'Tu sesión ha expirado.');
        }

        // 3. Decodificamos y comprobamos el rol
        $userData = json_decode($userJson);

        // ¡Este es el chequeo de seguridad!
        if ($userData && $userData->rol === 'admin') {
            // ¡Es admin! Devolvemos true.
            return true;
        }

        // 4. No es admin. Le redirigimos a la página principal con un error.
        return redirect()->route('principal', ['sesionId' => $sesionId])
                         ->with('error-admin', 'Acceso denegado. No tienes permisos de administrador.');
    }


    /**
     * Muestra el listado de muebles.
     */
    // !! 2. AÑADIMOS Request $request Y EL CONTROL DE SEGURIDAD !!
    public function index(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $muebles = $this->getMuebles();
        // Pasamos los muebles a la vista del panel de administración
        // !! 3. PASAMOS EL sesionId A LA VISTA (para los enlaces) !!
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.index', compact('muebles', 'sesionId'));
    }

    /**
     * Muestra el formulario para crear un nuevo mueble.
     */
    // !! 2. AÑADIMOS Request $request Y EL CONTROL DE SEGURIDAD !!
    public function create(Request $request)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        // !! 3. PASAMOS EL sesionId A LA VISTA (para el formulario) !!
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.create', compact('sesionId'));
    }

    /**
     * Guarda un nuevo mueble en la cookie.
     */
    public function store(Request $request)
    {
        // !! 2. AÑADIMOS EL CONTROL DE SEGURIDAD !!
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $muebles = $this->getMuebles();

        // !! CORRECCIÓN: Lógica restaurada !!
        $maxId = $muebles->max(fn($m) => $m->getId()) ?? 0;

        $newMuebleData = $request->all();
        $newMuebleData['id'] = $maxId + 1;

        // Creamos una instancia de Furniture para mantener la consistencia
        $newMueble = new Furniture(
            $newMuebleData['id'],
            (int)$newMuebleData['category_id'],
            $newMuebleData['name'],
            $newMuebleData['description'],
            (float)$newMuebleData['price'],
            (int)$newMuebleData['stock'],
            $newMuebleData['materials'] ?? '',
            $newMuebleData['dimensions'] ?? '',
            $newMuebleData['main_color'],
            $request->has('is_salient'),
            $newMuebleData['images'] ?? ['default.jpg']
        );
        // !! Fin de la lógica restaurada !!

        $muebles->push($newMueble);
        // FIX: Guardar la colección actualizada en la cookie.
        $this->saveMuebles($muebles);

        // !! 3. AÑADIMOS el sesionId A LA REDIRECCIÓN !!
        $sesionId = $request->input('sesionId');
        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble creado correctamente.');
    }

    /**
     * Muestra los detalles de un mueble específico.
     */
    // !! 2. AÑADIMOS Request $request Y EL CONTROL DE SEGURIDAD !!
    public function show(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $muebles = $this->getMuebles();

        // !! CORRECCIÓN: La lógica de búsqueda que faltaba !!
        // FIX: Usar una función de callback para acceder al método getId().
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404);
        }

        // !! 3. PASAMOS EL sesionId A LA VISTA (para los enlaces) !!
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.show', compact('mueble', 'sesionId'));
    }


    /**
     * Muestra el formulario para editar un mueble.
     */
    // !! 2. AÑADIMOS Request $request Y EL CONTROL DE SEGURIDAD !!
    public function edit(Request $request, $id)
    {
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $muebles = $this->getMuebles();

        // !! CORRECCIÓN: La lógica de búsqueda que faltaba !!
        // FIX: Usar una función de callback para acceder al método getId().
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404);
        }

        // !! 3. PASAMOS EL sesionId A LA VISTA (para el formulario) !!
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        return view('admin.muebles.edit', compact('mueble', 'sesionId'));
    }

    public function update(Request $request, $id)
    {
        // !! 2. AÑADIMOS EL CONTROL DE SEGURIDAD !!
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $muebles = $this->getMuebles();

        // !! CORRECCIÓN: Lógica restaurada !!
        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) {
            abort(404);
        }

        $mueble = $muebles[$muebleIndex];

        // Asignación de todos los campos del formulario
        $mueble->setName($request->input('name', $mueble->getName()));
        $mueble->setDescription($request->input('description', $mueble->getDescription()));
        $mueble->setPrice((float)$request->input('price', $mueble->getPrice()));
        $mueble->setMainColor($request->input('main_color', $mueble->getMainColor()));
        $mueble->setIsSalient($request->has('is_salient'));
        $mueble->setStock((int)$request->input('stock', $mueble->getStock()));
        $mueble->setCategoryId((int)$request->input('category_id', $mueble->getCategoryId()));

        // CAMPOS QUE FALTABAN
        $mueble->setMaterials($request->input('materials', $mueble->getMaterials()));
        $mueble->setDimensions($request->input('dimensions', $mueble->getDimensions()));
        // !! Fin de la lógica restaurada !!

        $muebles[$muebleIndex] = $mueble;
        $this->saveMuebles($muebles);

        // !! 3. AÑADIMOS el sesionId A LA REDIRECCIÓN !!
        $sesionId = $request->input('sesionId');
        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble actualizado correctamente.');
    }

    /**
     * Elimina un mueble de la cookie.
     */
    public function destroy(Request $request, $id)
    {
        // !! 2. AÑADIMOS EL CONTROL DE SEGURIDAD !!
        $check = $this->checkAdmin($request);
        if ($check !== true) return $check; // Si no es admin, redirige

        $muebles = $this->getMuebles();

        // !! CORRECCIÓN: Lógica restaurada !!
        // FIX: Usar una función de callback para acceder al método getId().
        // !! CORRECCIÓN 2: Añadido ->values() para re-indexar la colección !!
        $muebles = $muebles->reject(fn($m) => $m->getId() === (int)$id)->values();

        $this->saveMuebles($muebles);

        // !! 3. AÑADIMOS el sesionId A LA REDIRECCIÓN !!
        // (El sesionId vendrá del formulario que envía el DELETE)
        $sesionId = $request->input('sesionId');
        return redirect()->route('admin.muebles.index', ['sesionId' => $sesionId])->with('success', 'Mueble eliminado correctamente.');
    }
}
