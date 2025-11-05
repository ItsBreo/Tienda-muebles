<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Furniture;

class CheckoutController extends Controller
{

    private $mueblesSessionKey = 'muebles_crud_session';


     // Muestra la página de resumen del pedido.

    public function index(Request $request)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $cart = Session::get('carrito_' . $user->id, []);
        $total = 0;
        foreach ($cart as $item) {
            $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;
            $precio = isset($item['precio']) ? (float)$item['precio'] : 0.0;
            $total += $cantidad * $precio;
        }

        return view('checkout.index', compact('cart', 'total', 'sesionId'));
    }

    /**
     * Procesa la "compra", resta el stock y vacía el carrito.
     */
    public function processCheckout(Request $request)
    {

        $sesionId = $request->input('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }


        $cartKey = 'carrito_' . $user->id;
        $cart = Session::get($cartKey, []);

        if (empty($cart)) {
            // Si el carrito está vacío, no hay nada que procesar
            return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('error', 'Tu carrito está vacío.');
        }

        // OBTENER LA "BASE DE DATOS" de muebles (de la sesión del AdminController)
        $allMuebles = collect(Session::get($this->mueblesSessionKey));


        foreach ($cart as $muebleId => $item) {
            $quantityInCart = (int)$item['cantidad'];

            // Buscamos el índice del mueble en la colección
            $index = $allMuebles->search(fn($mueble) => $mueble->getId() == $muebleId);

            if ($index !== false) {
                // Mueble encontrado, actualizamos su stock
                $mueble = $allMuebles[$index];

                // Usamos el setter del modelo, igual que en AdminController
                // (max(0, ...) evita que el stock sea negativo)
                $newStock = $mueble->getStock() - $quantityInCart;
                $mueble->setStock(max(0, $newStock));

                // Reemplazamos el mueble antiguo por el actualizado en la colección
                $allMuebles[$index] = $mueble;
            }
        }


        Session::put($this->mueblesSessionKey, $allMuebles);


        Session::forget($cartKey);

        // Redirigir a la página principal con un mensaje de éxito
        return redirect()->route('principal', ['sesionId' => $sesionId])
                         ->with('success', '¡Gracias por tu compra! El stock ha sido actualizado.');
    }

}
