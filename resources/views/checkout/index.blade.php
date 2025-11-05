@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-4">
    <h1>Checkout</h1>
    <p>Total: <strong>{{ number_format($total ?? 0, 2) }} €</strong></p>

    @if(empty($cart))
        <p>No hay artículos en el carrito.</p>
        <a href="{{ route('muebles.index', ['sesionId' => $sesionId]) }}" class="btn btn-primary">Seguir comprando</a>
    @else
        <a href="{{ route('carrito.show', ['sesionId' => $sesionId]) }}" class="btn btn-secondary">Volver al carrito</a>
        {{-- Aquí continuaría tu flujo de pago --}}
    @endif
</div>
@endsection
