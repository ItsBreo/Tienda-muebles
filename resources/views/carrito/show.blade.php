@extends('layouts.app')

@section('title', 'Tu Carrito de Compras')

@section('content')
    <div class="container py-4">

        <h1 class="mb-4 display-5">🛒 Resumen del Carrito</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-4 border-l-4 border-red-700 p-4">
                <h4 class="alert-heading fw-bold">⚠️ Compra no Procesada</h4>
                <p>La siguiente lista muestra los motivos por los cuales no se pudo finalizar la compra:</p>
                <ul class="list-disc ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <hr>
                <p class="mb-0">Por favor, edita las cantidades en el carrito o vacía el carrito para continuar.</p>
            </div>
        @endif


        @if (empty($cart))
            <div class="alert alert-info" role="alert">
                <p class="lead mb-0">No tienes muebles en el carrito.</p>
            </div>
            <a href="{{ route('muebles.index', ['sesionId' => $sesionId]) }}" class="btn btn-primary mt-3">Ver Catálogo</a>
        @else
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Mueble</th>
                                        <th class="text-end">Precio Unitario</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cart as $id => $item)
                                        <tr>

                                            <td>
                                                <h6 class="mb-0">{{ $item['nombre'] }}</h6>
                                            </td>
                                            <td class="text-end">{{ number_format($item['precio'], 2) }} €</td>
                                            <td class="text-center">{{ $item['cantidad'] }}</td>
                                            <td class="text-end fw-bold">{{ number_format($item['precio'] * $item['cantidad'], 2) }} €</td>
                                            <td class="text-center" style="width: 100px;">
                                                <form method="POST" action="{{ route('carrito.remove', ['mueble' => $id, 'sesionId' => $sesionId]) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Eliminar ítem">
                                                        <i class="bi bi-trash"></i> 🗑️
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4 align-items-center">
                <div class="col-md-6">
                    <a href="{{ route('muebles.index', ['sesionId' => $sesionId]) }}" class="btn btn-secondary">
                        &larr; Seguir comprando
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1">Subtotal: <span class="fw-bold">{{ number_format($subtotal, 2) }} €</span></p>
                    <p class="mb-1">Impuestos (10%): <span class="fw-bold">{{ number_format($impuestos, 2) }} €</span></p>
                    <h3 class="fw-bold mb-3">Total del Pedido: <span class="text-primary">{{ number_format($total, 2) }} €</span></h3>

                    <form method="POST" action="{{ route('carrito.clear', ['sesionId' => $sesionId]) }}" class="d-inline me-2">
                        @csrf
                        <button class="btn btn-outline-warning" type="submit">Vaciar Carrito</button>
                    </form>
                    <form method="POST" action="{{ route('carrito.save') }}" class="d-inline" >
                        @csrf
                        <input type="hidden" name="sesionId" value="{{ $sesionId }}">

                        <button type="submit" class="btn btn-success btn-lg">
                            Finalizar Compra &rarr;
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
