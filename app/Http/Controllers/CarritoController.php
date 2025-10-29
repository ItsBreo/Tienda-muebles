<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mueble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    public function index(Request $request)
    {
        $sesionId = $request->query('sesionId');

        $usuario = User::activeUserSesion($sesionId);

        if (!$usuario) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $carrito = Session::get('carrito_' . $usuario->id, []);
        $total = 0;

          if ($carrito) {
            foreach ($carrito as $car) {
                $total += $car['precio'] * $car['cantidad'];
            }
        }
    return view('carrito.index', compact('carrito', 'total', 'usuario', 'sesionId'));
    }
        public function add(Request $request, int $id)
           {
        $sesionId = $request->query('sesionId');
        $usuario = User::activeUserSesion($sesionId);

        if (!$usuario) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $request->validate([
            'cantidad' => 'required|int|min:1|max:10'
        ]);

        $cantidad = $request->cantidad;

        $mueble = Mueble::searchById($id);
        if (!$mueble) {
            return redirect()->route('muebles.index', ['sesionId' => $sesionId])->withErrors('Mueble no encontrado');
        }

        $carrito = Session::get('carrito_' . $usuario->id, []);

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad'] += $cantidad;
        } else {
            /* Sustituir esto por los valores apropiados para mueble
            $carrito[$id] = [
                'titulo' => $pelicula->getTitulo(),
                'precio' => $pelicula->getPrecio(),
                'cantidad' => $cantidad
            ];
            */
        }

        Session::put('carrito_' . $usuario->id, $carrito);
        return redirect()->route('carrito.index', ['sesionId' => $sesionId])->with('success', 'Mueble agregado al carrito');
    }

        public function remove(Request $request, $id)
    {
        $sesionId = $request->query('sesionId');
        $usuario = User::activeUserSesion($sesionId);

        if (!$usuario) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $carrito = Session::get('carrito_' . $usuario->id, []);

        unset($carrito[$id]);
        Session::put('carrito_' . $usuario->id, $carrito);
        return redirect()->route('carrito.index', ['sesionId' => $sesionId])->with('success', 'Mueble eliminado del carrito');
    }

    public function empty(Request $request)
    {
        $sesionId = $request->query('sesionId');
        $usuario = User::activeUserSesion($sesionId);

        if (!$usuario) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        Session::forget('carrito_' . $usuario->id);
        return redirect()->route('carrito.index', ['sesionId' => $sesionId])->with('success', 'Carrito vaciado');
    }


}