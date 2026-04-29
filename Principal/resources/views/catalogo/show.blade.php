@extends('layouts.app')

@section('title', $category ? $category->name : 'Todos los Muebles')

@section('content')
    <div class="text-center mb-5">
        @if ($category)
            <h1>{{ $category->name }}</h1>
            <p class="lead">{{ $category->description }}</p>
        @else
            <h1>Todos los Muebles</h1>
            <p class="lead">Explora nuestro catálogo completo</p>
        @endif
    </div>

    <div class="card card-body mb-4" style="background-color: #FFFBFE; border-color: #EEE;">
        <form method="GET" action="{{ request()->url() }}">
            <div class="row g-3 align-items-end">

                <div class="col-lg-3 col-md-6">
                    <label for="search" class="form-label small">Buscar:</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}"
                        placeholder="Ej: Sofá, Mesa...">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label small">Precio:</label>
                    <div class="input-group">
                        <input type="number" name="price_min" class="form-control" value="{{ request('price_min') }}"
                            placeholder="Min" min="0" step="0.01">
                        <input type="number" name="price_max" class="form-control" value="{{ request('price_max') }}"
                            placeholder="Max" min="0" step="0.01">
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label for="color" class="form-label small">Color:</label>
                    <select name="color" id="color" class="form-select">
                        <option value="">Todos</option>
                        @foreach ($colors as $color)
                            <option value="{{ $color }}" {{ request('color') == $color ? 'selected' : '' }}>
                                {{ $color }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label for="sort" class="form-label small">Ordenar por:</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="novelty" {{ request('sort', 'novelty') == 'novelty' ? 'selected' : '' }}>Novedad
                        </option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Precio (Asc)
                        </option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Precio (Desc)
                        </option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nombre (A-Z)
                        </option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nombre (Z-A)
                        </option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 d-grid">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row g-4">
        @forelse ($muebles as $mueble)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="{{ asset('images/' . $mueble->getMainImage()) }}" class="card-img-top"
                        alt="{{ $mueble->name }}" style="height: 250px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $mueble->name }}</h5>

                        @if ($mueble->isSalient)
                            <span class="badge bg-success mb-2 align-self-start">Destacado</span>
                        @endif

                        <p class="h4 fw-bold text-end" style="color: #565254;">
                            {{-- TODO: Formatear moneda según la cookie de preferencias (Apartado 1) --}}
                            {{ number_format($mueble->price, 2) }} €
                        </p>
                        <a href="{{ route('muebles.show', $mueble->id) }}" class="btn btn-primary w-100 mt-auto">
                            Ver Detalle
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p class="alert alert-warning text-center">No se encontraron muebles con esos criterios.</p>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        @php
            $current = $muebles->currentPage();
            $last = $muebles->lastPage();
            $from = $muebles->firstItem() ?: 0;
            $to = $muebles->lastItem() ?: 0;
            $total = $muebles->total();
        @endphp

        <nav aria-label="Paginación del catálogo">
            <ul class="pagination mb-0">
                {{-- Anterior --}}
                @if ($current <= 1)
                    <li class="page-item disabled"><span class="page-link">Anterior</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $muebles->url($current - 1) }}" rel="prev">Anterior</a>
                    </li>
                @endif

                {{-- Números simples (muestra hasta 7 botones con el actual centrado) --}}
                @php
                    $start = max(1, $current - 3);
                    $end = min($last, $current + 3);
                @endphp

                @if ($start > 1)
                    <li class="page-item"><a class="page-link" href="{{ $muebles->url(1) }}">1</a></li>
                    @if ($start > 2)
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    @endif
                @endif

                @for ($p = $start; $p <= $end; $p++)
                    @if ($p == $current)
                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $p }}</span>
                        </li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $muebles->url($p) }}">{{ $p }}</a>
                        </li>
                    @endif
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    @endif
                    <li class="page-item"><a class="page-link" href="{{ $muebles->url($last) }}">{{ $last }}</a>
                    </li>
                @endif

                {{-- Siguiente --}}
                @if ($current >= $last)
                    <li class="page-item disabled"><span class="page-link">Siguiente</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $muebles->url($current + 1) }}"
                            rel="next">Siguiente</a></li>
                @endif
            </ul>
        </nav>
    </div>

    <div class="catalogo-pagination text-center mt-3">
        <div class="pagination-info text-muted small">
            Mostrando {{ $from }} a {{ $to }} de {{ $total }} resultados
        </div>
    </div>
@endsection
