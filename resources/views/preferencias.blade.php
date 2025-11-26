@extends('layouts.app')


@section('title', 'Configurar Preferencias')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        <div class="card shadow-lg border-0" style="background-color: var(--bs-tertiary-bg);">
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Mis Preferencias</h2>
                <p class="text-center text-muted mb-4">
                    Personaliza tu experiencia. Estos ajustes se guardarán en este navegador.
                </p>


                <form action="{{ route('preferencias.update', parameters: ['sesionId' => $sesionId]) }}" method="POST">
                    @csrf

                    <!-- 1. Tema Visual -->
                    <div class="mb-3">
                        <label for="tema" class="form-label">Tema Visual</label>
                        <select class="form-select form-select-lg" id="tema" name="tema">
                            <option value="claro">Claro (Por defecto)</option>
                            <option value="oscuro">Oscuro</option>
                        </select>
                    </div>

                    <!-- 2. Moneda -->
                    <div class="mb-3">
                        <label for="moneda" class="form-label">Moneda</label>
                        <select class="form-select form-select-lg" id="moneda" name="moneda">
                            <option value="USD">Dólar (USD)</option>
                            <option value="EUR">Euro (EUR)</option>
                            <option value="GBP">Libra (GBP)</option>
                        </select>
                    </div>

                    <!-- 3. Tamaño (Productos por Página) -->
                    <div class="mb-3">
                        <label for="tamaño" class="form-label fw-bold">Productos por Página:</label>
                        <select name="tamaño" id="tamaño" class="form-select">
                            <option value="6" {{ ($preferencias['tamaño'] ?? 0) == 6 ? 'selected' : '' }}>6 Productos</option>
                            <option value="12" {{ ($preferencias['tamaño'] ?? 0) == 12 ? 'selected' : '' }}>12 Productos (Defecto)</option>
                            <option value="24" {{ ($preferencias['tamaño'] ?? 0) == 24 ? 'selected' : '' }}>24 Productos</option>
                        </select>
                        <div class="form-text">Cuántos productos se mostrarán en el catálogo antes de cambiar de página.</div>
                    </div>

                    <!-- Botón de Guardar -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Guardar Preferencias</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

