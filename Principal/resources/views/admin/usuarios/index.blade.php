<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Usuarios - Tienda</title>
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
                                <button type="submit" class="btn btn-link nav-link p-2" style="text-decoration: none;">Cerrar Sesion</button>
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
                <h1 class="mb-4 text-primary">Gestión de Usuarios</h1>

                @if (session('success'))
                    <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-primary mb-0">Listado de Usuarios</h5>
                            <a href="{{ route('admin.usuarios.create') }}" class="btn btn-primary">Nuevo Usuario</a>
                        </div>

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
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2"
                                                         style="width: 35px; height: 35px; font-size: 0.9rem;">
                                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                                    </div>
                                                    {{ $user->name }} {{ $user->surname }}
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if(isset($user->role) && $user->role->name === 'Admin')
                                                    <span class="badge bg-primary">Administrador</span>
                                                @elseif(isset($user->role) && $user->role->name === 'Gestor')
                                                    <span class="badge bg-warning text-dark">Gestor</span>
                                                @else
                                                    <span class="badge bg-secondary">Cliente</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->last_login_at)
                                                    <span class="text-success fw-bold">{{ $user->last_login_at->diffForHumans() }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $user->last_login_at->format('d/m/Y H:i') }}</small>
                                                @else
                                                    <span class="text-muted fst-italic">Nunca</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="{{ route('admin.usuarios.show', ['usuario' => $user->id]) }}"
                                                       class="btn btn-sm btn-info text-white">Ver</a>
                                                    <a href="{{ route('admin.usuarios.edit', ['usuario' => $user->id]) }}"
                                                       class="btn btn-sm btn-secondary">Editar</a>
                                                    @if((int) session('user.id') !== (int) $user->id)
                                                        <form action="{{ route('admin.usuarios.destroy', ['usuario' => $user->id]) }}"
                                                              method="POST" class="d-inline"
                                                              onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $user->name }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                                        </form>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-danger" disabled title="No puedes eliminar tu propio usuario">Eliminar</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-secondary py-4">No hay usuarios para mostrar.</td>
                                        </tr>
                                    @endforelse
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
