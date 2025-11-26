@extends('layouts.app')

{{-- $mueble y $preferencias vienen de MuebleController@show --}}

@section('title', $mueble->name)

@section('content')

    @php
        // Aseguramos compatibilidad: algunas plantillas usan $m y el controlador pasa $mueble
        $m = $mueble ?? null;
    @endphp

    <div class="row g-5">

        <div class="col-lg-6">
            <img src="{{ asset($mueble->main_image) }}"
                 class="img-fluid rounded shadow-sm w-100 mb-3"
                 alt="{{ $mueble->name }}"
                 id="main-image"
                 style="height: 450px; object-fit: cover; border: 1px solid #EEE;">

            <div class="d-flex flex-wrap">
                @foreach(json_decode($mueble->images) as $image)
                    <img src="{{ asset($image) }}"
                         class="img-thumbnail me-2 mb-2"
                         style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                         alt="Miniatura"
                         onclick="document.getElementById('main-image').src = this.src">
                @endforeach
            </div>
        </div>

        <div class="col-lg-6">
            <h1>{{ $mueble->name }}</h1>

            @if($mueble->is_salient)
                <span class="badge bg-success mb-2">Producto Destacado</span>
            @endif

            <p class="lead">{{ $mueble->description }}</p>

            <p class="display-4 fw-bold" style="color: var(--bs-body-color);">

                {{ number_format($mueble->price, 2) }} {{ $preferencias['moneda'] }}
            </p>

            <hr>

            <h4>Detalles del Producto</h4>
            <ul class="list-unstyled">
                <li><strong>Material:</strong> {{ $mueble->materials }}</li>
                <li><strong>Dimensiones:</strong> {{ $mueble->dimensions }}</li>
                <li><strong>Color:</strong> {{ $mueble->main_color }}</li>
                <li><strong>Stock:</strong>
                    @if($mueble->stock > 0)
                        <span class="badge bg-success">En Stock ({{ $mueble->stock }} unidades)</span>
                    @else
                        <span class="badge bg-danger">Agotado</span>
                    @endif
                </li>
            </ul>

            <hr>

            @if($mueble->stock > 0)
                <form action="{{ route('carrito.add', ['mueble' => $mueble->id]) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Cantidad:</label>
                            <input type="number" name="quantity" id="quantity" class="form-control"
                                   value="1" min="1" max="{{ $mueble->stock }}">
                        </div>
                        <div class="col-md-8 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg mt-auto">
                                Añadir al Carrito
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <p class="alert alert-warning">Este producto no está disponible actualmente.</p>
            @endif

            <div class="mt-4">

                <a href="{{ route('categorias.show', ['id' => $mueble->category_id]) }}" class="btn btn-outline-secondary">&larr; Volver a la Categoría</a>
                <a href="{{ route('muebles.index') }}" class="btn btn-outline-secondary">Ver todos los muebles</a>
            </div>
        </div>
    </div>
@endsection
