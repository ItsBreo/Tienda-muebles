@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-4 text-white"
                        style="background: linear-gradient(160deg, #3f5efb 0%, #1f2a44 100%);">
                        <div class="p-4 p-lg-5 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-light text-dark mb-3">Perfil de usuario</span>
                                <h1 class="h3 mb-2">{{ $user['name'] ?? 'Usuario' }} {{ $user['surname'] ?? '' }}</h1>
                                <p class="mb-4 text-white-50">
                                    Gestiona y consulta la información principal de tu cuenta dentro de la tienda.
                                </p>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                    style="width: 64px; height: 64px; background-color: rgba(255,255,255,.18); font-size: 1.35rem;">
                                    {{ strtoupper(substr($user['name'] ?? 'U', 0, 1)) }}{{ strtoupper(substr($user['surname'] ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="small text-white-50">Rol actual</div>
                                    <div class="fw-semibold">{{ $user['rol_name'] ?? 'Cliente' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="p-4 p-lg-5">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                                <div>
                                    <h2 class="h4 mb-1">Datos de la cuenta</h2>
                                </div>
                                <a href="{{ route('preferencias.show', ['sesionId' => $sesionId]) }}" class="btn btn-outline-primary">
                                    Ver preferencias
                                </a>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-body-tertiary">
                                        <div class="text-muted small mb-1">Nombre</div>
                                        <div class="fw-semibold">{{ $user['name'] ?? 'No disponible' }}</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-body-tertiary">
                                        <div class="text-muted small mb-1">Apellidos</div>
                                        <div class="fw-semibold">{{ $user['surname'] ?? 'No disponible' }}</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-body-tertiary">
                                        <div class="text-muted small mb-1">Correo electrónico</div>
                                        <div class="fw-semibold">{{ $user['email'] ?? 'No disponible' }}</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-body-tertiary">
                                        <div class="text-muted small mb-1">Rol</div>
                                        <div class="fw-semibold">{{ $user['rol_name'] ?? 'Cliente' }}</div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="border rounded-4 p-3 h-100 bg-body-tertiary">
                                        <div class="text-muted small mb-1">ID de usuario</div>
                                        <div class="fw-semibold">#{{ $user['id'] ?? 'N/D' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
