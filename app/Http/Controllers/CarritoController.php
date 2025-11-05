<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CarritoController extends Controller
{
    // Clave de la sesión donde guardamos la "BD" de muebles del Admin
    private $mueblesSessionKey = 'muebles_crud_session';

    public function show(Request $request)
    {

        $sesionId = $request->query('sesionId');

        $user = User::activeUserSesion($sesionId);

        if (!$user) {

            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        // Obtenemos el carrito
        $cart = Session::get('carrito_' . $user->id, []);

        // Obtenemos los datos "en vivo"
        // Leemos la lista de muebles que el Admin edita.
        $allMuebles = collect(Session::get($this->mueblesSessionKey, Furniture::getMockData()));

        $total = 0;
        $cartWithLiveData = []; // Un nuevo array para la vista

        if (!empty($cart)) {
            foreach ($cart as $id => $item) {
                // Encontrar el mueble en la BD de sesión
                $liveMueble = $allMuebles->first(fn($m) => $m->getId() == $id);

                if ($liveMueble) {
                    // El mueble existe, usamos sus datos
                    $cantidad = (int) $item['cantidad'];
                    $precioVivo = $liveMueble->getPrice();
                    $nombreVivo = $liveMueble->getName();

                    // Re-creamos el array del carrito para la vista
                    $cartWithLiveData[$id] = [
                        'id' => $id,
                        'nombre' => $nombreVivo,
                        'precio' => $precioVivo,
                        'cantidad' => $cantidad,
                        // Añadimos la imagen para la vista
                        'imagen' => $liveMueble->getMainImage(),
                    ];
                    $total += $precioVivo * $cantidad;
                }
                // Si $liveMueble es null (p.ej. admin lo borró),
                // el item simplemente no aparecerá en el carrito.
            }
        }


        return view('carrito.show', [
            'cart' => $cartWithLiveData,
            'total' => $total,
            'user' => $user,
            'sesionId' => $sesionId
        ]);
    }

    public function add(Request $request, int $id)
    {

        $sesionId = $request->input('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $quantity = (int) $request->input('cantidad', $request->input('quantity', 1));
        if ($quantity < 1) {
            $quantity = 1;
        }


        $allMuebles = collect(Session::get($this->mueblesSessionKey, Furniture::getMockData()));
        $furniture = $allMuebles->first(fn($m) => $m->getId() == (int)$id);

        if (!$furniture) {
            return redirect()->route('muebles.index', ['sesionId' => $sesionId])->withErrors('Mueble no encontrado');
        }

        $cart = Session::get('carrito_' . $user->id, []);

        if (isset($cart[$id])) {
            $cart[$id]['cantidad'] = (int)$cart[$id]['cantidad'] + $quantity;
        } else {

            $cart[$id] = [
                'id' => $furniture->getId(),
                'nombre' => $furniture->getName(),
                'precio' => $furniture->getPrice(),
                'cantidad' => $quantity,
                'imagen' => $furniture->getMainImage()
            ];

        }

        Session::put('carrito_' . $user->id, $cart);
        return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Mueble agregado al carrito');
    }

        public function remove(Request $request, $id)
    {

        $sesionId = $request->input('sesionId');
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

        $sesionId = $request->input('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {

            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        Session::forget('carrito_' . $user->id);
        return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Carrito vaciado');
    }
}
