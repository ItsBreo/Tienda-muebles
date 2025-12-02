@extends('layouts.app')

@section('title', 'Categorías')

@section('content')
    <div class="text-center mb-5">
        <h1>Categorías de Muebles</h1>
        <p class="lead">Explora nuestras colecciones por categoría</p>
    </div>

    <div class="row g-4">
        @forelse ($categories as $category)

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title">{{ $category->name }}</h5>
                        <p class="card-text small">{{ $category->description }}</p>
                        <a href="{{ route('categorias.show', ['id' => $category->id, 'sesionId' => $activeSesionId])   }}" class="btn btn-primary mt-auto">
                            Ver Muebles
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p class="text-center">No hay categorías disponibles.</p>
            </div>
        @endforelse
    </div>
@endsection

