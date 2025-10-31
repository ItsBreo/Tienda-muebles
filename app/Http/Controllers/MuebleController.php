<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Models\Furniture;

// Controlador de Muebles
class MuebleController extends Controller
{
    public function index(Request $request)
    {
        // redirigimos al listado general
        return redirect()->route('muebles.index');
    }

    public function show(Request $request, $id)
    {
        // Obtenemos mueble por id
        $mueble = Furniture::findById((int)$id);

        // Guardamos cookie por mueble (nombre: mueble_{id})
        Cookie::queue("mueble_{$mueble->getId()}", json_encode($mueble), 60 * 24 * 30);

        // Vista detalle: pasamos el mueble a la vista
        return view('muebles.show', ['mueble' => $mueble]);
    }
}
