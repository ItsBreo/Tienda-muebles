@extends('layouts.app')

@section('title', 'Muebles')

@section('content')
    <h1>Muebles</h1>

    <form class="row g-2 mb-3" method="GET" action="{{ route('muebles.index') }}">
        <div class="col-md-3">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar...">
        </div>
        <div class="col-md-2">
            <select name="category" class="form-select">
                <option value="">Todas</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->getId() }}" @if (request('category') == $c->getId()) selected @endif>
                        {{ $c->getName() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="min_price" value="{{ request('min_price') }}" class="form-control"
                placeholder="Min €">
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="max_price" value="{{ request('max_price') }}" class="form-control"
                placeholder="Max €">
        </div>
        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="">Orden</option>
                <option value="price_asc" @if (request('sort') == 'price_asc') selected @endif>Precio ↑</option>
                <option value="price_desc" @if (request('sort') == 'price_desc') selected @endif>Precio ↓</option>
                <option value="name_asc" @if (request('sort') == 'name_asc') selected @endif>Nombre A-Z</option>
                <option value="name_desc" @if (request('sort') == 'name_desc') selected @endif>Nombre Z-A</option>
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <div class="row">
        @foreach ($muebles as $m)
            <div class="col-md-4">
                <div class="card mb-3 @if (request()->cookie('pref_theme') === 'dark') dark @endif">
                    <img src="/images/{{ $m->getMainImage() }}" class="card-img-top" alt="{{ $m->getName() }}">
                    <div class="card-body">
                        <h5 class="card-title">{{ $m->getName() }}</h5>
                        <p class="card-text">{{ \Illuminate\Support\Str::limit($m->getDescription(), 100) }}</p>
                        <p class="mb-1"><strong>{{ number_format($m->getPrice(), 2) }} €</strong></p>
                        <a href="{{ route('muebles.show', ['id' => $m->getId(), 'sesionId' => $activeSesionId]) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                        <form action="{{ route('carrito.add', $m->getId()) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="qty" value="1">
                            <button class="btn btn-sm btn-success">Añadir</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- PAGINADOR INLINE: reemplaza {{ $muebles->links() }} -->
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
