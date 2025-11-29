@extends('layouts.app')

@section('title', 'Muebles')

@section('content')
    <h1>Muebles</h1>

    <form class="row g-2 mb-3 align-items-end" method="GET" action="{{ route('muebles.index') }}">

        {{-- Mantenemos el ID de sesión oculto para no perderlo al filtrar --}}
        @if(isset($activeSesionId))
            <input type="hidden" name="sesionId" value="{{ $activeSesionId }}">
        @endif

        {{-- Buscador --}}
        <div class="col-md-3">
            <label class="form-label small">Buscar</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Nombre o descripción...">
        </div>

        {{-- Categoría --}}
        <div class="col-md-2">
            <label class="form-label small">Categoría</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">Todas</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" @selected(request('category') == $c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- NUEVO: Color --}}
        <div class="col-md-2">
            <label class="form-label small">Color</label>
            <select name="color" class="form-select form-select-sm">
                <option value="">Todos</option>
                @foreach ($colors as $colorOption)
                    <option value="{{ $colorOption }}" @selected(request('color') == $colorOption)>
                        {{ $colorOption }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Precios --}}
        <div class="col-md-1">
            <label class="form-label small">Min €</label>
            <input type="number" step="1" name="min_price" value="{{ request('min_price') }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-1">
            <label class="form-label small">Max €</label>
            <input type="number" step="1" name="max_price" value="{{ request('max_price') }}" class="form-control form-control-sm">
        </div>

        {{-- Ordenación --}}
        <div class="col-md-2">
            <label class="form-label small">Orden</label>
            <select name="sort" class="form-select form-select-sm">
                <option value="">Defecto</option>
                {{-- Nuevas opciones de fecha --}}
                <option value="date_new" @selected(request('sort') == 'date_new')>Novedades</option>
                <option value="date_old" @selected(request('sort') == 'date_old')>Más antiguos</option>
                <hr>
                <option value="price_asc" @selected(request('sort') == 'price_asc')>Precio: Bajo a Alto</option>
                <option value="price_desc" @selected(request('sort') == 'price_desc')>Precio: Alto a Bajo</option>
                <option value="name_asc" @selected(request('sort') == 'name_asc')>Nombre (A-Z)</option>
                <option value="name_desc" @selected(request('sort') == 'name_desc')>Nombre (Z-A)</option>
            </select>
        </div>

        {{-- Botón --}}
        <div class="col-md-1">
            <button class="btn btn-primary btn-sm w-100">Filtrar</button>
        </div>
    </form>

    <div class="row">
        @foreach ($muebles as $m)
            <div class="col-md-4">
                <div class="card mb-3 @if (request()->cookie('pref_theme') === 'dark') dark @endif">
                    <img src="{{ asset($m->getMainImage()) }}" class="card-img-top" alt="{{ $m->name }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $m->name }}</h5>
                        <p class="card-text">{{ \Illuminate\Support\Str::limit($m->description, 100) }}</p>
                        <p class="mb-1"><strong>{{ number_format($m->price) }} €</strong></p>
                        <a href="{{ route('muebles.show', ['id' => $m->id, 'sesionId' => $activeSesionId]) }}" class="btn btn-sm btn-outline-primary">Ver</a>

                        @if ($m->stock > 0)
                            <form action="{{ route('carrito.add', ['mueble' => $m->id, 'sesionId' => $activeSesionId]) }}" method="POST" class="d-inline ms-1">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-success">Añadir</button>
                            </form>
                        @else
                            <button class="btn btn-sm btn-secondary d-inline ms-1" disabled>Agotado</button>
                        @endif

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- PAGINADOR INLINE -->
    <div class="d-flex justify-content-center">
        @php
            $current = $muebles->currentPage();
            $last = $muebles->lastPage();
            $from = $muebles->firstItem() ?: 0;
            $to = $muebles->lastItem() ?: 0;
            $total = $muebles->total();
            $start = max(1, $current - 3);
            $end = min($last, $current + 3);
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
