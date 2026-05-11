<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PerfilController extends Controller
{
    public function show(Request $request)
    {
        $sesionId = $request->input('sesionId') ?? $request->query('sesionId');
        $user = Session::get('user');

        if (!$user) {
            return redirect()->route('login.show')->withErrors([
                'error' => 'Debes iniciar sesión para ver tu perfil.',
            ]);
        }

        $comprasRecientes = Cart::query()
            ->where('user_id', $user['id'])
            ->latest()
            ->take(3)
            ->get();

        $comprasDetalles = [];
        $cartIds = $comprasRecientes->pluck('id')->all();
        if (!empty($cartIds)) {
            $lineas = DB::table('cart_furniture')
                ->select(['cart_id', 'furniture_id', 'quantity'])
                ->whereIn('cart_id', $cartIds)
                ->get();

            $furnitureIds = $lineas->pluck('furniture_id')->unique()->values()->all();
            $apiFurniture = app(\App\Services\ApiFurnitureService::class);
            $furnitureById = $apiFurniture->getMultipleFurniture($furnitureIds);

            foreach ($cartIds as $cartId) {
                $lineasCart = $lineas->where('cart_id', $cartId);
                $itemsCount = (int) $lineasCart->sum('quantity');

                $topNames = $lineasCart
                    ->pluck('furniture_id')
                    ->unique()
                    ->take(3)
                    ->map(fn ($fid) => $furnitureById->get($fid)['name'] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                $comprasDetalles[$cartId] = [
                    'items_count' => $itemsCount,
                    'top_names' => $topNames,
                ];
            }
        }

        $resumenCompras = [
            'total_compras' => Cart::where('user_id', $user['id'])->count(),
            'gasto_total' => (float) Cart::where('user_id', $user['id'])->sum('total_price'),
            'ultima_compra' => $comprasRecientes->first()?->created_at,
        ];

        return view('perfil.show', compact('user', 'sesionId', 'resumenCompras', 'comprasRecientes', 'comprasDetalles'));
    }
}
