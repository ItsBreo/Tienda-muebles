<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --bs-primary: #565254; --bs-secondary: #7A7D7D; --bs-timberwolf: #D0CFCF; --bs-snow: #FFFBFE; }
        body { background-color: var(--bs-timberwolf); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar-custom { background-color: var(--bs-primary) !important; color: var(--bs-snow); }
        .navbar-custom .navbar-brand, .navbar-custom .nav-link, .navbar-custom .btn-link { color: var(--bs-snow) !important; }
        .sidebar { width: 250px; background-color: var(--bs-secondary); min-height: calc(100vh - 56px); padding-top: 1rem; }
        .sidebar .nav-link { color: var(--bs-snow); padding: 0.75rem 1rem; border-left: 3px solid transparent; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: var(--bs-primary); background-color: var(--bs-timberwolf); border-left-color: var(--bs-primary); font-weight: 600; }
        .footer-custom { background-color: var(--bs-primary); color: var(--bs-snow); margin-top: auto; padding: 1rem 0; }
    </style>
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('admin.muebles.index') }}">
                    <img src="{{ asset('images/JJDAY.png') }}" alt="Logo" style="height: 30px;" class="me-2">
                    Panel de Control
                </a>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item"><span class="nav-link">{{ session('user.rol_name') }}</span></li>
                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link p-2" style="text-decoration: none;">Cerrar Sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container-fluid flex-grow-1">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="nav flex-column nav-pills">
                    <a class="nav-link" href="{{ route('principal') }}">Ir a la tienda</a>
                    <a class="nav-link" href="{{ route('admin.muebles.index') }}">Muebles</a>
                    <a class="nav-link" href="{{ route('admin.categorias.index') }}">Categorías</a>
                    <a class="nav-link active" href="{{ route('admin.usuarios.index') }}">Usuarios</a>
                    <a class="nav-link" href="{{ route('admin.logs') }}">Logs de Actividad</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Editar Usuario: {{ $user->name }}</h1>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Datos del Usuario</h5>
                        <hr>
                        <form action="{{ route('admin.usuarios.update', ['usuario' => $user->id]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="surname" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control" id="surname" name="surname" value="{{ old('surname', $user->surname) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="role_id" class="form-label">Rol</label>
                                    <select class="form-select" id="role_id" name="role_id" required>
                                        <option value="">Seleccione un rol...</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id }}" @if(old('role_id', $user->role_id) == $role->id) selected @endif>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Nueva Contraseña <small class="text-muted">(opcional)</small></label>
                                    <input type="password" class="form-control" id="password" name="password" minlength="4">
                                    <small class="text-muted">Deja vacío para mantener la actual.</small>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                                    <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer-custom text-center">
        <div class="container"><p class="mb-0">&copy; {{ date('Y') }} Tienda de Muebles.</p></div>
    </footer>

</body>
</html>
