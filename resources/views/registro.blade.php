@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header text-center py-3 bg-secondary text-white">
                <h4 class="mb-0">Registro de Nuevo Cliente</h4>
            </div>
            <div class="card-body p-4 bg-light">

                {{-- !! CORRECCIÓN: Sintaxis action arreglada y ruta correcta !! --}}
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="name" class="form-label text-secondary fw-bold">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" required value="{{ old('name') }}" autofocus>
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Apellidos --}}
                    <div class="mb-3">
                        <label for="surname" class="form-label text-secondary fw-bold">Apellidos</label>
                        <input type="text" class="form-control" id="surname" name="surname" required value="{{ old('surname') }}">
                        @error('surname') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary fw-bold">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required value="{{ old('email') }}">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="mb-3">
                        <label for="password" class="form-label text-secondary fw-bold">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    {{-- Confirmar Contraseña --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label text-secondary fw-bold">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    {{-- Botones --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Registrarse</button>
                    </div>

                </form>
                <div class="mt-3 text-center">
                    <a href="{{ route('login.show') }}" class="text-decoration-none text-muted">
                        ¿Ya tienes una cuenta? <span class="fw-bold">Inicia Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
