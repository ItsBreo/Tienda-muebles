@extends('layouts.app')

@section('title', 'Tu Carrito de Compras')

@section('content')
    <div class="container py-4">

        <h1 class="mb-4 display-5">🛒 Resumen de tu Carrito</h1>

        {{-- Mostrar mensajes de sesión (ej: "Mueble agregado al carrito") --}}
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

        {{-- 🛑 Usamos $cart, la variable que pasaste desde el CarritoController --}}
        @if (empty($cart))
            <div class="alert alert-info" role="alert">
                <p class="lead mb-0">No tienes **muebles** en el carrito. ¡Añade algunos!</p>
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
                                            {{-- Usamos la clave 'nombre' que definiste en el controlador --}}
                                            <td>
                                                <h6 class="mb-0">{{ $item['nombre'] }}</h6>
                                            </td>
                                            <td class="text-end">{{ number_format($item['precio'], 2) }} €</td>
                                            <td class="text-center">{{ $item['cantidad'] }}</td>
                                            <td class="text-end fw-bold">{{ number_format($item['precio'] * $item['cantidad'], 2) }} €</td>
                                            <td class="text-center" style="width: 100px;">
                                                <form method="POST" action="{{ route('carrito.remove', ['id' => $id, 'sesionId' => $sesionId]) }}">
                                                    @csrf
                                                    {{-- Usamos el método DELETE para ser más RESTful, aunque Laravel lo simule con POST --}}
                                                    @method('DELETE') 
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
                    <h3 class="fw-bold mb-3">Total del Pedido: <span class="text-primary">{{ number_format($total, 2) }} €</span></h3>
                    
                    <form method="POST" action="{{ route('carrito.clear', ['sesionId' => $sesionId]) }}" class="d-inline me-2">
                        @csrf
                        @method('DELETE') {{-- Usamos DELETE para la acción de vaciar --}}
                        <button class="btn btn-outline-warning" type="submit">Vaciar Carrito</button>
                    </form>

                    <a href="{{ route('checkout.index', ['sesionId' => $sesionId]) }}" class="btn btn-success btn-lg">
                        Finalizar Compra &rarr;
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection