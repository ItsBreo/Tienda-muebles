<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    public function show(Request $request)
    {
        $sesionId = $request->query('sesionId');

        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $cart = Session::get('carrito_' . $user->id, []);
        $total = 0;

          if ($cart) {
            foreach ($cart as $c) {
                $total += $c['precio'] * $c['quantity'];
            }
        }

    return view('carrito.index', compact('cart', 'total', 'usuario', 'sesionId'));
    }
        public function add(Request $request, int $id)
           {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $request->validate([
            'cantidad' => 'required|int|min:1|max:10'
        ]);

        $quantity = $request->quantity;

        $furniture = Furniture::($id);
        if (!$furniture) {
            return redirect()->route('muebles.index', ['sesionId' => $sesionId])->withErrors('Mueble no encontrado');
        }

        $cart = Session::get('carrito_' . $user->id, []);

        if (isset($cart[$id])) {
            $cart[$id]['cantidad'] += $quantity;
        } else {
             //Sustituir esto por los valores apropiados para mueble
            $cart[$id] = [
                'nombre' => $furniture->getName(),
                'precio' => $furniture->getPrice(),
                'cantidad' => $quantity
            ];

        }

        Session::put('carrito_' . $user->id, $cart);
        return redirect()->route('carrito.index', ['sesionId' => $sesionId])->with('success', 'Mueble agregado al carrito');
    }

        public function remove(Request $request, $id)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $cart = Session::get('carrito_' . $user->id, []);

        unset($cart[$id]);
        Session::put('carrito_' . $user->id, $cart);
        return redirect()->route('carrito.index', ['sesionId' => $sesionId])->with('success', 'Mueble eliminado del carrito');
    }

    public function clear(Request $request)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        Session::forget('carrito_' . $user->id);
        return redirect()->route('carrito.index', ['sesionId' => $sesionId])->with('success', 'Carrito vaciado');
    }


}
