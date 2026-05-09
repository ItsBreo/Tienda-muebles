<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CarritoController extends Controller
{
	private const TAX_RATE = 0.10;

	private $mueblesSessionKey = 'muebles_crud_session';

	/**
	 * Muestra el carrito del usuario autenticado, sincronizando precios con la BD,
	 * y realiza el cálculo de subtotal, impuestos y total.
	 */
	public function show(Request $request)
	{
		$sesionId = $request->query('sesionId');
		$user = Session::get('user');

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
		}

		// Obtener el carrito de la sesión específica del usuario
		$cart = Session::get('carrito_' . $user['id'], []);

		$subtotal = 0;
		$cartWithLiveData = [];

		if (!empty($cart)) {
			$idsInCart = array_keys($cart);

			// CONSULTA A LA API: Obtener la información de los muebles
            $apiFurniture = app(\App\Services\ApiFurnitureService::class);
			$allFurniture = $apiFurniture->getMultipleFurniture($idsInCart);

			foreach ($cart as $id => $item) {
				$liveFurniture = $allFurniture->get($id);

				if ($liveFurniture) {
					$cantidad = (int) $item['cantidad'];
					$precioVivo = $liveFurniture['price'];
					$nombreVivo = $liveFurniture['name'];

					$lineTotal = $precioVivo * $cantidad;
					$subtotal += $lineTotal;

					// Re-creamos el array del carrito para la vista
					$cartWithLiveData[$id] = [
						'id' => $id,
						'nombre' => $nombreVivo,
						'precio' => $precioVivo,
						'cantidad' => $cantidad,
						'imagen' => $liveFurniture['main_image'] ?? 'images/default.png',
						'line_total' => $lineTotal,
					];
				}
			}
		}

		$impuestos = $subtotal * self::TAX_RATE;
		$total = $subtotal + $impuestos;

		return view('carrito.show', [
			'cart' => $cartWithLiveData,
			'subtotal' => $subtotal,
			'impuestos' => $impuestos,
			'total' => $total,
			'user' => $user,
			'sesionId' => $sesionId
		]);
	}


	public function add(Request $request, int $id)
	{
		$sesionId = $request->input('sesionId');
		$user = Session::get('user');

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
		}

		$quantity = (int) $request->input('cantidad', $request->input('quantity', 1));
		if ($quantity < 1) {
			$quantity = 1;
		}

		// CONSULTA A LA API
        $apiFurniture = app(\App\Services\ApiFurnitureService::class);
		$furniture = $apiFurniture->getFurnitureDetail($id);

		if (!$furniture) {
			return redirect()->back()->withErrors('Mueble no encontrado en el catálogo.');
		}

		$cart = Session::get('carrito_' . $user['id'], []);
		$currentQuantity = isset($cart[$id]) ? (int)$cart[$id]['cantidad'] : 0;
		$newQuantity = $currentQuantity + $quantity;

		// Validacion de Stock
		if ($newQuantity > $furniture['stock']) {
			return redirect()->back()->withErrors(['stockError' => "Stock insuficiente. Solo quedan {$furniture['stock']} unidades."]);
		}

		// Si hay suficiente stock, actualizamos el carrito de sesión
		if (isset($cart[$id])) {
			$cart[$id]['cantidad'] = $newQuantity;
		} else {
			$cart[$id] = [
				'id' => $furniture['id'],
				'nombre' => $furniture['name'],
				'precio' => $furniture['price'],
				'cantidad' => $quantity,
				'imagen' => $furniture['main_image'] ?? 'images/default.png'
			];
		}

		Session::put('carrito_' . $user['id'], $cart);
		return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Mueble agregado al carrito');
	}

	/**
	 * Actualiza la cantidad de un producto en el carrito de la sesión.
	 */
	public function update(Request $request, $id)
	{
		$sesionId = $request->input('sesionId');
		$user = Session::get('user');

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
		}

		$quantity = (int) $request->input('cantidad');
		if ($quantity < 1) {
			return redirect()->route('carrito.show', ['sesionId' => $sesionId])->withErrors('La cantidad debe ser al menos 1.');
		}

		$cart = Session::get('carrito_' . $user['id'], []);

		if (!isset($cart[$id])) {
			return redirect()->route('carrito.show', ['sesionId' => $sesionId])->withErrors('El mueble no está en el carrito.');
		}

        $apiFurniture = app(\App\Services\ApiFurnitureService::class);
		$furniture = $apiFurniture->getFurnitureDetail($id);

		if (!$furniture || $quantity > $furniture['stock']) {
			return redirect()->route('carrito.show', ['sesionId' => $sesionId])->withErrors(['stockError' => "Stock insuficiente. Solo quedan " . ($furniture['stock'] ?? 0) . " unidades."]);
		}

		$cart[$id]['cantidad'] = $quantity;
		Session::put('carrito_' . $user['id'], $cart);

		return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Cantidad actualizada');
	}

	/**
	 * Elimina un producto del carrito de la sesión del usuario.
	 */
	public function remove(Request $request, $id)
	{
		$sesionId = $request->input('sesionId');
		$user = Session::get('user');

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
		}

		$cart = Session::get('carrito_' . $user['id'], []);

		unset($cart[$id]);
		Session::put('carrito_' . $user['id'], $cart);
		return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Mueble eliminado del carrito');
	}

	/**
	 * Vacía el carrito de la sesión del usuario autenticado.
	 */
	public function clear(Request $request)
	{
		$sesionId = $request->input('sesionId');
		$user = Session::get('user');

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
		}

		Session::forget('carrito_' . $user['id']);
		return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Carrito vaciado');
	}

	/**
	 * Guarda el carrito de sesión en la BD (Persistencia) y vacía el carrito actual.
	 */
	public function saveOnBD(Request $request)
	{
		$sesionId = $request->input('sesionId');
		$user = Session::get('user');

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión para guardar el carrito.']);
		}

		// Si el usuario es válido, lo inyectamos (temporalmente comentado hasta implementar ApiAuth)
		// if (!Auth::check()) {
		// 	Auth::login($user);
		// }

		$carritoSesion = Session::get('carrito_' . $user['id'], []);

		if (empty($carritoSesion)) {
			return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('error', 'El carrito está vacío.');
		}

		// Preparar datos y VALIDACIÓN DE STOCK antes de abrir transacción
		$subtotal = 0;
		$itemsToStore = [];
		$idsInCart = array_keys($carritoSesion);
        $apiFurniture = app(\App\Services\ApiFurnitureService::class);
		$allFurniture = $apiFurniture->getMultipleFurniture($idsInCart);

        $stockErrors = []; // Array para recoger todos los errores de stock

		foreach ($carritoSesion as $id => $item) {
			$liveFurniture = $allFurniture->get($id);

			if ($liveFurniture) {
				$cantidad = (int) $item['cantidad'];

                if ($liveFurniture['stock'] < $cantidad) {
                    $stockErrors[] = "Stock insuficiente para '{$liveFurniture['name']}'. Solicitaste {$cantidad}, pero solo quedan {$liveFurniture['stock']} unidades.";
                    continue;
                }

				$precioUnitario = $liveFurniture['price'];
				$subtotal += $cantidad * $precioUnitario;

				$itemsToStore[] = [
					'producto_id' => $id,
					'cantidad' => $cantidad,
					'precio_unitario' => $precioUnitario,
				];
			}
		}
            // Control de stock
        if (!empty($stockErrors)) {
            return redirect()->route('carrito.show', ['sesionId' => $sesionId])
                ->withErrors($stockErrors)
                ->with('error', 'La compra no se ha procesado debido a problemas de stock. Por favor, ajusta las cantidades.');
        }


		$impuestos = $subtotal * self::TAX_RATE;
		$total = $subtotal + $impuestos;

		// Transacción de BD (Solo si NO hubo errores de stock)
		try {
			Log::info('--- INICIO TRANSACCION SAVE ON BD --- User ID: ' . $user['id']);

			DB::beginTransaction();

			// Guardar Carrito (Historial) localmente en Principal
			$newCart = Cart::create([
				'user_id' => $user['id'],
				'sesion_id' => $sesionId,
				'total_price' => $total,
			]);

            if (!$newCart) {
                throw new \Exception("Error al crear el registro del carrito. Verifique permisos de tabla 'carts' o campos obligatorios nulos.");
            }

			// Guardar Detalles (sin usar foreign keys estrictas ni el modelo Furniture local)
			foreach ($itemsToStore as $item) {
                DB::table('cart_furniture')->insert([
                    'cart_id' => $newCart->id,
                    'furniture_id' => $item['producto_id'],
                    'quantity' => $item['cantidad'],
                    'unit_price' => $item['precio_unitario'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
			}

            // Llamar a la API de muebles para restar el stock de forma atómica
            $stockUpdated = $apiFurniture->decrementStock($itemsToStore);

            if (!$stockUpdated) {
                throw new \Exception("La compra se registró localmente pero no se pudo actualizar el stock en la API de muebles.");
            }

			DB::commit();
			Log::info('--- COMMIT EXITOSO ---');

		} catch (\Exception $e) {
			DB::rollBack();
			Log::error('--- ROLLBACK POR EXCEPCION --- Mensaje: ' . $e->getMessage());
			return redirect()->route('carrito.show', ['sesionId' => $sesionId])
				->withErrors('Error de base de datos al finalizar la compra. Mensaje: ' . $e->getMessage());
		}

		// Vaciar el carrito actual de la sesión
		Session::forget('carrito_' . $user['id']);

		return redirect()->route('carrito.show', ['sesionId' => $sesionId])
			->with('success', '¡Compra guardada correctamente y stock actualizado!');
	}

    // ── Stripe Checkout ─────────────────────────────────────────────────────────

    public function stripeCheckout(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
        }

        $cart = Session::get('carrito_' . $user['id'], []);
        if (empty($cart)) {
            return redirect()->route('carrito.show')->with('error', 'Tu carrito está vacío.');
        }

        // Configurar Stripe con la clave secreta del .env
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = [];
        foreach ($cart as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['nombre'],
                    ],
                    'unit_amount' => (int)($item['precio'] * 100), // Stripe usa céntimos
                ],
                'quantity' => $item['cantidad'],
            ];
        }

        // Añadimos el IVA como un concepto separado si quieres, o Stripe Tax. 
        // Por simplicidad en este ejercicio, los precios unitarios ya podrían llevarlo o añadir un item de "Impuestos".
        // Aquí vamos a añadir una línea de "Impuestos (10%)" si hay subtotal.
        $subtotal = 0;
        foreach($cart as $item) { $subtotal += $item['precio'] * $item['cantidad']; }
        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Impuestos (10%)',
                ],
                'unit_amount' => (int)($subtotal * 0.10 * 100),
            ],
            'quantity' => 1,
        ];

        try {
            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('checkout.success') . '?stripe_id={CHECKOUT_SESSION_ID}&sesionId=' . $request->input('sesionId'),
                'cancel_url' => route('checkout.cancel'),
            ]);

            return redirect($checkoutSession->url);
        } catch (\Exception $e) {
            return redirect()->route('carrito.show')->with('error', 'Error al conectar con Stripe: ' . $e->getMessage());
        }
    }

    public function checkoutSuccess(Request $request)
    {
        $user = Session::get('user');
        if (!$user) {
            return redirect()->route('principal');
        }

        // En un entorno real, aquí verificaríamos la sesión con Stripe usando $request->session_id
        
        // Reutilizamos la lógica de guardado en BD
        $dummyRequest = new Request();
        $dummyRequest->merge(['sesionId' => $request->query('sesionId')]);
        
        return $this->saveOnBD($dummyRequest);
    }

    public function checkoutCancel()
    {
        return redirect()->route('carrito.show')->with('error', 'El pago fue cancelado o hubo un problema.');
    }
}
