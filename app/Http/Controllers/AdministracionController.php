<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AdministracionController extends Controller
{
    private $cookieName = 'muebles_crud';
    private $cookieMinutes = 60 * 24 * 7; // 1 semana

    /**
     * Obtiene los muebles desde la cookie o los datos mock.
     */
    private function getMuebles()
    {
        $mueblesJson = Cookie::get($this->cookieName);
        if ($mueblesJson) {
            // Decodificamos y convertimos a una colección de objetos Furniture
            return collect(json_decode($mueblesJson, true))->map(function ($item) {
                return new Furniture(
                    $item['id'], // id
                    $item['category_id'], // categoryId
                    $item['name'], // name
                    $item['description'], // description
                    $item['price'], // price
                    $item['stock'], // stock
                    $item['materials'] ?? '', // materials (con valor por defecto)
                    $item['dimensions'] ?? '', // dimensions (con valor por defecto)
                    $item['main_color'], // mainColor
                    $item['is_salient'], // isSalient
                    $item['images'] // images
                );
            });
        }
        return collect(Furniture::getMockData());
    }

    /**
     * Guarda la colección de muebles en la cookie.
     */
    private function saveMuebles($muebles)
    {
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
        $this->saveMuebles($muebles);

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un mueble.
     */
    public function edit($id)
    {
        $muebles = $this->getMuebles();
        $mueble = $muebles->firstWhere('id', (int)$id);

        if (!$mueble) {
            abort(404);
        }

        return view('admin.muebles.edit', compact('mueble'));
    }

    /**
     * Actualiza un mueble en la cookie.
     */
    public function update(Request $request, $id)
    {
        $muebles = $this->getMuebles();
        $muebleIndex = $muebles->search(fn($m) => $m->getId() == (int)$id);

        if ($muebleIndex === false) {
            abort(404);
        }

        $mueble = $muebles[$muebleIndex];
        $mueble->setName($request->input('name', $mueble->getName()));
        $mueble->setDescription($request->input('description', $mueble->getDescription()));
        $mueble->setPrice((float)$request->input('price', $mueble->getPrice()));
        $mueble->setMainColor($request->input('main_color', $mueble->getMainColor()));
        $mueble->setIsSalient($request->has('is_salient'));
        $mueble->setStock((int)$request->input('stock', $mueble->getStock()));
        $mueble->setCategoryId((int)$request->input('category_id', $mueble->getCategoryId()));

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
        $muebles = $muebles->reject(fn($m) => $m->getId() == (int)$id)->values();
        $this->saveMuebles($muebles);

        return redirect()->route('admin.muebles.index')->with('success', 'Mueble eliminado correctamente.');
    }
}
