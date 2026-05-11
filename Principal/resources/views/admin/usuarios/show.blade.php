<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Usuario - Tienda</title>
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
        .detail-label { font-weight: 600; color: var(--bs-secondary); }
        .avatar { width: 80px; height: 80px; font-size: 1.5rem; }
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-primary mb-0">Detalle del Usuario</h1>
                    <div>
                        <a href="{{ route('admin.usuarios.edit', ['usuario' => $user->id]) }}" class="btn btn-secondary">Editar</a>
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-3 avatar">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $user->name }} {{ $user->surname }}</h4>
                                <small class="text-muted">ID #{{ $user->id }}</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-6"><p><span class="detail-label">Email:</span> {{ $user->email }}</p></div>
                            <div class="col-md-6">
                                <p>
                                    <span class="detail-label">Rol:</span>
                                    @if(isset($user->role) && $user->role->name === 'Admin')
                                        <span class="badge bg-primary">Administrador</span>
                                    @elseif(isset($user->role) && $user->role->name === 'Gestor')
                                        <span class="badge bg-warning text-dark">Gestor</span>
                                    @else
                                        <span class="badge bg-secondary">Cliente</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p>
                                    <span class="detail-label">Último acceso:</span>
                                    @if($user->last_login_at)
                                        {{ $user->last_login_at->format('d/m/Y H:i') }}
                                        <small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                                    @else
                                        <span class="fst-italic text-muted">Nunca</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><span class="detail-label">Registrado:</span> {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/D' }}</p>
                            </div>
                        </div>
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
