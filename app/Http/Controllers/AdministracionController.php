<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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

            // 3. SI EXISTEN, rehidratamos los arrays a Objetos Furniture
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
     * Muestra el listado de muebles.
     */
    public function index()
    {
        $muebles = $this->getMuebles();
        // Pasamos los muebles a la vista del panel de administración
        return view('admin.muebles.index', compact('muebles'));
    }

    /**
     * Muestra el formulario para crear un nuevo mueble.
     */
    public function create()
    {
        return view('admin.muebles.create');
    }

    /**
     * Guarda un nuevo mueble en la cookie.
     */
    public function store(Request $request)
    {
        $muebles = $this->getMuebles();
        $maxId = $muebles->max('id') ?? 0;

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

        $muebles->push($newMueble);
        // FIX: Guardar la colección actualizada en la cookie.
        $this->saveMuebles($muebles);

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble creado correctamente.');
    }

    /**
     * Muestra los detalles de un mueble específico.
     */
    public function show($id)
    {
        $muebles = $this->getMuebles();
        // FIX: Usar una función de callback para acceder al método getId().
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404);
        }

        return view('admin.muebles.show', compact('mueble'));
    }


    /**
     * Muestra el formulario para editar un mueble.
     */
    public function edit($id)
    {
        $muebles = $this->getMuebles();
        // FIX: Usar una función de callback para acceder al método getId().
        $mueble = $muebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$mueble) {
            abort(404);
        }

        return view('admin.muebles.edit', compact('mueble'));
    }

    public function update(Request $request, $id)
    {
        $muebles = $this->getMuebles();
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

        $muebles[$muebleIndex] = $mueble;
        $this->saveMuebles($muebles);

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble actualizado correctamente.');
    }

    /**
     * Elimina un mueble de la cookie.
     */
    public function destroy($id)
    {
        $muebles = $this->getMuebles();
        // FIX: Usar una función de callback para acceder al método getId().
        $muebles = $muebles->reject(fn($m) => $m->getId() == (int)$id);
        $this->saveMuebles($muebles);

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble eliminado correctamente.');
    }
}
