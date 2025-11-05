<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\Furniture;

class CheckoutController extends Controller
{
    // Clave de la sesión donde guardamos la "BD" de muebles del Admin
    private $mueblesSessionKey = 'muebles_crud_session';

    public function index(Request $request)
    {
        $sesionId = $request->query('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        // Obtenemos el carrito
        $cart = Session::get('carrito_' . $user->id, []);


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
                        'imagen' => $liveMueble->getMainImage(),
                    ];
                    $total += $precioVivo * $cantidad; //
                }
            }
        }


        return view('checkout.index', [
            'cart' => $cartWithLiveData,
            'total' => $total,
            'sesionId' => $sesionId
        ]);
    }

    /**
     * Procesa la "compra", resta el stock y vacía el carrito.
     */
    public function processCheckout(Request $request)
    {
        // Usamos input() porque esto vendrá de un formulario POST
        $sesionId = $request->input('sesionId');
        $user = User::activeUserSesion($sesionId);

        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        // Obtener el carrito del usuario
        $cartKey = 'carrito_' . $user->id;
        $cart = Session::get($cartKey, []);

        if (empty($cart)) {
            // Si el carrito está vacío, no hay nada que procesar
            return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('error', 'Tu carrito está vacío.');
        }

        // Obtener los datos de muebles (de la sesión del AdminController)
        $allMuebles = collect(Session::get($this->mueblesSessionKey, Furniture::getMockData()));

        // Restar el stock
        foreach ($cart as $muebleId => $item) {
            $quantityInCart = (int)$item['cantidad'];

            // Buscamos el índice del mueble en la colección
            $index = $allMuebles->search(fn($mueble) => $mueble->getId() == $muebleId);

            if ($index !== false) {
                // Mueble encontrado, actualizamos su stock
                $mueble = $allMuebles[$index];

                // Usamos el setter del modelo
                // (max(0, ...) evita que el stock sea negativo)
                $newStock = $mueble->getStock() - $quantityInCart;
                $mueble->setStock(max(0, $newStock));

                // Reemplazamos el mueble antiguo por el actualizado en la colección
                $allMuebles[$index] = $mueble;
            }
        }

        // Actualizamos la colección de muebles en la sesión
        Session::put($this->mueblesSessionKey, $allMuebles);

        // vaciamos el carrito del usuario
        Session::forget($cartKey);

        // Redirigir a la página principal con un mensaje de éxito
        return redirect()->route('principal', ['sesionId' => $sesionId])
                         ->with('success', '¡Gracias por tu compra! El stock ha sido actualizado.');
    }

}
