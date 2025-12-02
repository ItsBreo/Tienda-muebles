@extends('layouts.app')

@section('title', $mueble->name)

@section('content')
    {{--
        NOTA: Ya no necesitamos el bloque @php aquí.
        El controlador nos pasa directamente: $stockTotal, $enCarrito y $stockDisponible.
    --}}

    <div class="row g-5">
        {{-- COLUMNA IZQUIERDA: IMÁGENES --}}
        <div class="col-lg-6">
            <img src="{{ asset($mueble->getMainImage()) }}"
                 class="img-fluid rounded shadow-sm w-100 mb-3"
                 alt="{{ $mueble->name }}"
                 id="main-image"
                 style="height: 450px; object-fit: cover; border: 1px solid #EEE;">

            <div class="d-flex flex-wrap">
                @foreach($mueble->images as $image)
                    <img src="{{ asset($image->image_path) }}"
                         class="img-thumbnail me-2 mb-2"
                         style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                         alt="Miniatura"
                         onclick="document.getElementById('main-image').src = '{{ asset($image->image_path) }}'">
                @endforeach
            </div>
        </div>

        {{-- COLUMNA DERECHA: INFO --}}
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

            <h4>Detalles</h4>
            <ul class="list-unstyled">
                <li><strong>Material:</strong> {{ $mueble->materials }}</li>
                <li><strong>Dimensiones:</strong> {{ $mueble->dimensions }}</li>
                <li><strong>Color:</strong> {{ $mueble->main_color }}</li>

                {{-- INDICADOR DE ESTADO (Arriba) --}}
                <li class="mt-2">
                    <strong>Estado:</strong>
                    @if($stockDisponible > 0)
                        <span class="badge bg-success">Disponible</span>
                        <small class="text-muted ms-2">
                            (Quedan {{ $stockDisponible }} ud.)
                        </small>
                    @else
                        <span class="badge bg-danger">Agotado</span>
                    @endif
                </li>
            </ul>

            <hr>

            {{-- BLOQUE DE COMPRA O AVISO --}}
            @if($stockDisponible > 0)
                <form action="{{ route('carrito.add', ['mueble' => $mueble->id]) }}" method="POST">
                    @csrf
                    {{-- Usamos activeSesionId que viene del array sesionData --}}
                    <input type="hidden" name="sesionId" value="{{ $activeSesionId }}">

                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label for="quantity" class="form-label fw-bold">Cantidad</label>
                            <input type="number"
                                   name="quantity"
                                   id="quantity"
                                   class="form-control @error('stockError') is-invalid @enderror"
                                   value="1"
                                   min="1"
                                   max="{{ $stockDisponible }}">
                        </div>

                        <div class="col-md-8">
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="bi bi-cart-plus"></i> Añadir al Carrito
                            </button>
                        </div>
                    </div>

                    @error('stockError')
                        <div class="alert alert-danger mt-3">
                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
                        </div>
                    @enderror
                </form>
            @else
                {{-- BLOQUE CUANDO NO HAY STOCK DISPONIBLE --}}

                @if($stockTotal > 0 && $enCarrito >= $stockTotal)
                    {{-- CASO 1: ESTÁ EN EL CARRITO --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">

                            {{-- TEXTO AGOTADO: Ancho completo y padding grande --}}
                            <h3 class="mb-4">
                                <div class="bg-danger text-white fs-3 p-4 rounded-3 w-100 fw-bold">
                                    AGOTADO
                                </div>
                            </h3>

                            {{-- Botón Warning --}}
                            <a href="{{ route('carrito.show', ['sesionId' => $activeSesionId]) }}" class="btn btn-warning btn-lg fw-bold shadow">
                                <i class="bi bi-cart-check-fill text-dark"></i> Ir al Carrito
                            </a>
                        </div>
                    </div>
                @else
                    {{-- CASO 2: REALMENTE AGOTADO EN TIENDA --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">

                            {{-- TEXTO AGOTADO: Ancho completo y padding grande --}}
                            <h3 class="mb-4">
                                <div class="bg-danger text-white fs-3 p-4 rounded-3 w-100 fw-bold">
                                    SE HAN AGOTADO LAS EXISTENCIAS
                                </div>
                            </h3>

                            {{-- MENSAJE MEJORADO --}}
                            <div class="alert alert-light border d-flex align-items-center justify-content-center p-3 shadow-sm rounded-3">
                                <i class="bi bi-emoji-frown fs-3 text-secondary me-3"></i>
                                <span class="text-secondary fw-medium fs-5">Lamentamos que no pueda comprar este producto :(</span>
                            </div>

                        </div>
                    </div>
                @endif

            @endif

            <div class="mt-4">
                <a href="{{ route('categorias.show', ['id' => $mueble->category_id, 'sesionId' => $activeSesionId]) }}" class="btn btn-outline-secondary">&larr; Volver</a>
                <a href="{{ route('muebles.index', ['sesionId' => $activeSesionId]) }}" class="btn btn-outline-secondary">Catálogo</a>
            </div>
        </div>
    </div>
@endsection
