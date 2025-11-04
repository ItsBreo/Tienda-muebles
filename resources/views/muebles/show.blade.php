@extends('layouts.app')

{{-- $mueble, $activeSesionId, $activeUser, y $preferencias vienen de MuebleController@show --}}

@section('title', $mueble->getName())

@section('content')

    @php
        // Aseguramos compatibilidad: algunas plantillas usan $m y el controlador pasa $mueble
        $m = $mueble ?? null;
    @endphp

    <div class="row g-5">

        <div class="col-lg-6">
            <img src="{{ asset($mueble->getMainImage()) }}"
                 class="img-fluid rounded shadow-sm w-100 mb-3"
                 alt="{{ $mueble->getName() }}"
                 id="main-image"
                 style="height: 450px; object-fit: cover; border: 1px solid #EEE;">

            <div class="d-flex flex-wrap">
                @foreach($mueble->getImages() as $image)
                    <img src="{{ asset('/' . $image) }}"
                         class="img-thumbnail me-2 mb-2"
                         style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                         alt="Miniatura"
                         onclick="document.getElementById('main-image').src = this.src">
                @endforeach
            </div>
        </div>

        <div class="col-lg-6">
            <h1>{{ $mueble->getName() }}</h1>

            @if($mueble->isSalient())
                <span class="badge bg-success mb-2">Producto Destacado</span>
            @endif

            <p class="lead">{{ $mueble->getDescription() }}</p>

            <p class="display-4 fw-bold" style="color: var(--bs-body-color);">

                {{ $mueble->getFormattedPrice($preferencias['moneda']) }}
            </p>

            <hr>

            <h4>Detalles del Producto</h4>
            <ul class="list-unstyled">
                <li><strong>Material:</strong> {{ $mueble->getMaterials() }}</li>
                <li><strong>Dimensiones:</strong> {{ $mueble->getDimensions() }}</li>
                <li><strong>Color:</strong> {{ $mueble->getMainColor() }}</li>
                <li><strong>Stock:</strong>
                    @if($mueble->getStock() > 0)
                        <span class="badge bg-success">En Stock ({{ $mueble->getStock() }} unidades)</span>
                    @else
                        <span class="badge bg-danger">Agotado</span>
                    @endif
                </li>
            </ul>

            <hr>

            @if($mueble->getStock() > 0)
                <form action="{{ route('carrito.add', ['mueble' => $mueble->getId(), 'sesionId' => $activeSesionId]) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label">Cantidad:</label>
                            <input type="number" name="quantity" id="quantity" class="form-control"
                                   value="1" min="1" max="{{ $mueble->getStock() }}">
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

                <a href="{{ route('categorias.show', ['id' => $mueble->getCategoryId(), 'sesionId' => $activeSesionId]) }}" class="btn btn-outline-secondary">&larr; Volver a la Categoría</a>
                <a href="{{ route('muebles.index', ['sesionId' => $activeSesionId]) }}" class="btn btn-outline-secondary">Ver todos los muebles</a>
            </div>
        </div>
    </div>
@endsection

