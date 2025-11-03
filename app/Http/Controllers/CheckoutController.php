<?php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class CheckoutController extends Controller
{
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
}
