<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        :root { --bs-primary: #565254; --bs-secondary: #7A7D7D; --bs-timberwolf: #D0CFCF; --bs-snow: #FFFBFE; }
        body { background-color: var(--bs-timberwolf); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar-custom { background-color: var(--bs-primary) !important; color: var(--bs-snow); }
        .sidebar { width: 250px; background-color: var(--bs-secondary); min-height: calc(100vh - 56px); }
        .sidebar .nav-link { color: var(--bs-snow); }
        .sidebar .nav-link.active { background-color: var(--bs-timberwolf); color: var(--bs-primary); font-weight: bold; }
        .footer-custom { background-color: var(--bs-primary); color: var(--bs-snow); margin-top: auto; padding: 1rem 0; }
    </style>
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Panel de Control</a>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item"><span class="nav-link">Admin</span></li>
                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $sesionId }}">
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
            <div class="col-md-3 col-lg-2 sidebar pt-3">
                <div class="nav flex-column nav-pills">
                    <a class="nav-link" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Muebles</a>
                    <a class="nav-link" href="{{ route('admin.categorias.index', ['sesionId' => $sesionId]) }}">Categorías</a>
                    <a class="nav-link active" href="{{ route('admin.usuarios.index', ['sesionId' => $sesionId]) }}">Usuarios</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Listado de Usuarios</h1>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Último Acceso</th>
                                        <th>Registrado el</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>

                                            <td>
                                                <div class="d-flex align-items-center">
                                                    {{-- Avatar generado con las iniciales (opcional, queda bonito) --}}
                                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2"
                                                         style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    {{ $user->name }}
                                                </div>
                                            </td>

                                            <td>{{ $user->email }}</td>

                                            <td>
                                                @if($user->hasRole('Admin'))
                                                    <span class="badge bg-primary">Administrador</span>
                                                @else
                                                    <span class="badge bg-secondary">Cliente</span>
                                                @endif
                                            </td>

                                            <td>
                                                @if($user->last_login_at)
                                                    {{-- Muestra "Hace 2 horas" --}}
                                                    <span class="text-success fw-bold">
                                                        {{ $user->last_login_at->diffForHumans() }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">{{ $user->last_login_at->format('d/m/Y H:i') }}</small>
                                                @else
                                                    <span class="text-muted fst-italic">Nunca</span>
                                                @endif
                                            </td>

                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
