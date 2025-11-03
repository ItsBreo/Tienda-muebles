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

        // obtener carrito por usuario real
        $cart = Session::get('carrito_' . $user->id, []);
        $total = 0;
        if (!empty($cart)) {
            foreach ($cart as $c) {
                // la clave usada en el array es 'cantidad'
                $cantidad = isset($c['cantidad']) ? (int) $c['cantidad'] : 0;
                $precio = isset($c['precio']) ? (float) $c['precio'] : 0.0;
                $total += $precio * $cantidad;
            }
        }

        return view('carrito.show', compact('cart', 'total', 'user', 'sesionId'));
    }
    public function add(Request $request, int $id)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        // aseguramos cantidad mínima/por defecto y casteo a entero
        $quantity = (int) $request->input('cantidad', $request->input('quantity', 1));
        if ($quantity < 1) {
            $quantity = 1;
        }

        $furniture = Furniture::findById($id);
        if (!$furniture) {
            return redirect()->route('muebles.index', ['sesionId' => $sesionId])->withErrors('Mueble no encontrado');
        }

        $cart = Session::get('carrito_' . $user->id, []);

        if (isset($cart[$id])) {
            $cart[$id]['cantidad'] = (int)$cart[$id]['cantidad'] + $quantity;
        } else {
             //Sustituir esto por los valores apropiados para mueble
            $cart[$id] = [
                'nombre' => $furniture->getName(),
                'precio' => $furniture->getPrice(),
                'cantidad' => $quantity
            ];

        }

        // TODO: La entrada de muebles falla. El carrito queda vacio despues de agregar un mueble aunque exista la confirmación
        Session::put('carrito_' . $user->id, $cart);
        return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Mueble agregado al carrito');
    }

        public function remove(Request $request, $id)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $cart = Session::get('carrito_' . $user->id, []);

        unset($cart[$id]);
        Session::put('carrito_' . $user->id, $cart);
        return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Mueble eliminado del carrito');
    }

    public function clear(Request $request)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        Session::forget('carrito_' . $user->id);
        return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Carrito vaciado');
    }


}
