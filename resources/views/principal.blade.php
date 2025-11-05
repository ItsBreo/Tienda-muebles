@extends('layouts.app')

@section('title', 'Tienda de Muebles')

@section('content')
<div class="jumbotron py-4 mb-4">
    <div class="container">
        <h1 class="display-5">Bienvenido a la Tienda de Muebles</h1>
        <p class="lead">Catálogo de muebles de ejemplo, sin base de datos — todo con mock, cookies y sesiones.</p>
        <p>

            <a class="btn btn-primary" href="{{ route('muebles.index', ['sesionId' => $activeSesionId]) }}">Ver catálogo completo</a>
            <a class="btn btn-outline-secondary" href="{{ route('categorias.index', ['sesionId' => $activeSesionId]) }}">Explorar categorías</a>
        </p>
    </div>
</div>

<h2 class="mb-3">Categorías</h2>
<div class="row mb-4">
    @foreach($categories as $cat)
    <div class="col-md-3">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">{{ $cat->getName() }}</h5>
                <p class="card-text">{{ $cat->getDescription() }}</p>

                <a href="{{ route('categorias.show', ['id' => $cat->getId(), 'sesionId' => $activeSesionId]) }}" class="btn btn-sm btn-primary">Ver muebles</a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<h2 class="mb-3">Destacados</h2>
<div class="row">
    @forelse($featured as $m)
    <div class="col-md-4">
        <div class="card mb-3">
            <img src="{{ asset($m->getMainImage()) }}" class="card-img-top" alt="{{ $m->getName() }}">
            <div class="card-body">
                <h5 class="card-title">{{ $m->getName() }}</h5>
                <p class="card-text">{{ \Illuminate\Support\Str::limit($m->getDescription(), 90) }}</p>


                <p class="mb-1"><strong>{{ $m->getFormattedPrice($preferencias['moneda']) }}</strong></p>


                <a href="{{ route('muebles.show', ['id' => $m->getId(), 'sesionId' => $activeSesionId]) }}" class="btn btn-sm btn-outline-primary">Ver</a>

                @if ($m->getStock() > 0)
                            <form action="{{ route('carrito.add', ['mueble' => $m->getId(), 'sesionId' => $activeSesionId]) }}" method="POST" class="d-inline ms-1">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $activeSesionId }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">Añadir</button>
                            </form>
                @else
                            <button class="btn btn-sm btn-secondary d-inline ms-1" disabled>Agotado</button>
                @endif

            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <p>No hay muebles destacados.</p>
    </div>
    @endforelse
</div>
@endsection
