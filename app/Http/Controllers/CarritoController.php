<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Furniture; // Usamos el modelo Furniture real
use App\Models\Cart; // Usamos el modelo Cart
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Necesario para transacciones de BD
use Illuminate\Support\Facades\Log; // Para diagnóstico

class CarritoController extends Controller
{
	// Tasa de impuesto simulada (10%)
	private const TAX_RATE = 0.10;

	// La clave de sesión $mueblesSessionKey se mantiene pero se ignora, ya no es necesaria.
	private $mueblesSessionKey = 'muebles_crud_session';

	/**
	 * Muestra el carrito del usuario autenticado, sincronizando precios con la BD,
	 * y realiza el cálculo de subtotal, impuestos y total.
	 */
	public function show(Request $request)
	{
		$sesionId = $request->query('sesionId');
		$user = User::activeUserSesion($sesionId);

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión.']);
		}

		// 1. Obtener el carrito de la sesión específica del usuario
		$cart = Session::get('carrito_' . $user->id, []);

		$subtotal = 0;
		$cartWithLiveData = [];

		if (!empty($cart)) {
			$idsInCart = array_keys($cart);

			// 2. CONSULTA A LA BD: Obtener la información "en vivo" de Furniture
			$allFurniture = Furniture::whereIn('id', $idsInCart)->get()->keyBy('id');

			foreach ($cart as $id => $item) {
				$liveFurniture = $allFurniture->get($id);

				if ($liveFurniture) {
					// El mueble existe. Usamos sus propiedades reales: 'name' y 'price'.
					$cantidad = (int) $item['cantidad'];
					$precioVivo = $liveFurniture->price; // Propiedad 'price'
					$nombreVivo = $liveFurniture->name;   // Propiedad 'name'

					$lineTotal = $precioVivo * $cantidad;
					$subtotal += $lineTotal;

					// Re-creamos el array del carrito para la vista
					$cartWithLiveData[$id] = [
						'id' => $id,
						'nombre' => $nombreVivo,
						'precio' => $precioVivo,
						'cantidad' => $cantidad,
						'imagen' => $liveFurniture->getMainImage(), // Usamos el helper
						'line_total' => $lineTotal,
					];
				}
			}
		}

		// Cálculo de impuestos y total (Requisito 4)
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

	/**
	 * Agrega un producto (Furniture) al carrito de la sesión, incluyendo validación de stock.
	 */
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

		// CONSULTA A LA BD REAL
		$furniture = Furniture::find($id);

		if (!$furniture) {
			return redirect()->back()->withErrors('Mueble no encontrado en la base de datos.');
		}

		$cart = Session::get('carrito_' . $user->id, []);
		$currentQuantity = isset($cart[$id]) ? (int)$cart[$id]['cantidad'] : 0;
		$newQuantity = $currentQuantity + $quantity;

		// Validacion de Stock (Requisito 4)
		if ($newQuantity > $furniture->stock) {
			return redirect()->back()->withErrors(['stockError' => "Stock insuficiente. Solo quedan {$furniture->stock} unidades."]);
		}

		// Si hay suficiente stock, actualizamos el carrito de sesión
		if (isset($cart[$id])) {
			$cart[$id]['cantidad'] = $newQuantity;
		} else {
			$cart[$id] = [
				'id' => $furniture->id,
				'nombre' => $furniture->name,
				'precio' => $furniture->price,
				'cantidad' => $quantity,
				'imagen' => $furniture->getMainImage()
			];
		}

		Session::put('carrito_' . $user->id, $cart);
		return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('success', 'Mueble agregado al carrito');
	}

	/**
	 * Elimina un producto del carrito de la sesión del usuario.
	 */
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

	/**
	 * Vacía el carrito de la sesión del usuario autenticado.
	 */
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

	/**
	 * Guarda el carrito de sesión en la BD (Persistencia) y vacía el carrito actual.
	 */
	public function saveOnBD(Request $request)
	{
		// 1. Autenticación y obtención de datos
		$sesionId = $request->input('sesionId');
		$user = User::activeUserSesion($sesionId);

		if (!$user) {
			return redirect()->route('login.show')->withErrors(['errorCredenciales' => 'Debes iniciar sesión para guardar el carrito.']);
		}

		// CORRECCIÓN DE AUTH: Si el usuario es válido, lo inyectamos
		if (!Auth::check()) {
			Auth::login($user);
		}

		$carritoSesion = Session::get('carrito_' . $user->id, []);

		if (empty($carritoSesion)) {
			return redirect()->route('carrito.show', ['sesionId' => $sesionId])->with('error', 'El carrito está vacío.');
		}

		// 2. Recalculamos los totales con precios "en vivo" y preparamos para guardar
		$subtotal = 0;
		$itemsToStore = [];
		$idsInCart = array_keys($carritoSesion);
		$allFurniture = Furniture::whereIn('id', $idsInCart)->get()->keyBy('id');

		foreach ($carritoSesion as $id => $item) {
			$liveFurniture = $allFurniture->get($id);
			if ($liveFurniture) {
				$cantidad = (int) $item['cantidad'];
				$precioUnitario = $liveFurniture->price;

				$subtotal += $cantidad * $precioUnitario;

				$itemsToStore[] = [
					'producto_id' => $id,
					'cantidad' => $cantidad,
					'precio_unitario' => $precioUnitario,
				];
			}
		}

		$impuestos = $subtotal * self::TAX_RATE;
		$total = $subtotal + $impuestos;

		// 3. Transacción de BD
		try {
			// DIAGNÓSTICO: Registrar inicio de transacción
			Log::info('--- INICIO TRANSACCION SAVE ON BD --- User ID: ' . Auth::id());

			DB::beginTransaction();

			// 4. Creamos el registro en la tabla 'carritos'
			$newCart = Cart::create([
				'user_id' => Auth::id(), // ID del usuario autenticado
				'sesion_id' => $sesionId,
				'total_price' => $total,
			]);

			// DIAGNÓSTICO: Verificar que el carrito se creó
            if (!$newCart) {
                throw new \Exception("Cart::create() retornó NULL.");
            }
			Log::info('Carrito Creado con ID: ' . $newCart->id);

			// 5. Insertamos los productos en la tabla pivote 'cart_furniture'
			foreach ($itemsToStore as $item) {
				$newCart->productos()->attach($item['producto_id'], [
					// CORRECCIÓN FINAL: Usamos 'quantity' y 'unit_price' para coincidir con la migración
					'quantity' => $item['cantidad'],
					'unit_price' => $item['precio_unitario']
				]);
			}

			// 6. Confirmamos la transacción
			DB::commit();
			Log::info('--- COMMIT EXITOSO ---');

		} catch (\Exception $e) {
			DB::rollBack();
			// Devolvemos un mensaje de error detallado para que se vea en la vista
			Log::error('--- ROLLBACK POR EXCEPCION --- Mensaje: ' . $e->getMessage());
			return redirect()->route('carrito.show', ['sesionId' => $sesionId])
				->withErrors('Error de base de datos al finalizar la compra. Mensaje: ' . $e->getMessage());
		}


		// 7. Vaciar el carrito actual de la sesión
		Session::forget('carrito_' . $user->id);
		Log::info('--- CARRITO DE SESION VACIADO Y REDIRECCIONANDO ---');

		return redirect()->route('carrito.show', ['sesionId' => $sesionId])
			->with('success', '¡Compra guardada correctamente y carrito vaciado!');
	}
}
