@extends('layouts.app')

@section('title', 'Finalizar Compra')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1 class="mb-4">Finalizar Compra</h1>

            <div class="card shadow-sm border-0">
                <div class="card-header fs-5">
                    Resumen del Pedido
                </div>
                <div class="card-body">
                    @if (!empty($cart))
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th scope="col">Producto</th>
                                        <th scope="col">Precio</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- $cart es un array de ['id' => ['nombre' => ..., 'precio' => ..., 'cantidad' => ...]] --}}
                                    @foreach ($cart as $id => $item)
                                        <tr>
                                            <td>
                                                <h5 class="mb-0">{{ $item['nombre'] }}</h5>
                                            </td>
                                            <td>{{ number_format($item['precio'], 2, ',', '.') }} €</td>
                                            <td>{{ $item['cantidad'] }}</td>
                                            <td>{{ number_format($item['precio'] * $item['cantidad'], 2, ',', '.') }} €</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('carrito.show', ['sesionId' => $sesionId]) }}" class="btn btn-outline-secondary">Volver al Carrito</a>
                            <div class="text-end">
                                <h3 class="mb-3">Total: {{ number_format($total, 2, ',', '.') }} €</h3>

                                {{-- Formulario para Finalizar Compra --}}
                                <form action="{{ route('checkout.process') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="sesionId" value="{{ $sesionId }}">
                                    <button type="submit" class="btn btn-primary btn-lg">Confirmar Pedido y Pagar</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">Tu carrito está vacío.</div>
                        <a href="{{ route('muebles.index', ['sesionId' => $sesionId]) }}" class="btn btn-primary">Ver Catálogo</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
