<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administracion de Muebles - Tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        :root {
            --bs-davys-gray: #565254;
            --bs-gray-medium: #7A7D7D;
            --bs-timberwolf: #D0CFCF;
            --bs-snow: #FFFBFE;
            --bs-primary: var(--bs-davys-gray);
            --bs-secondary: var(--bs-gray-medium);
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bs-timberwolf);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar-custom {
            background-color: var(--bs-primary) !important;
            color: var(--bs-snow);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link,
        .navbar-custom .btn-link {
            color: var(--bs-snow) !important;
        }

        .sidebar {
            width: 250px;
            background-color: var(--bs-secondary);
            padding-top: 1rem;
            min-height: calc(100vh - 56px);
        }

        .sidebar .nav-link {
            color: var(--bs-snow);
            padding: 0.75rem 1rem;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: var(--bs-primary);
            background-color: var(--bs-timberwolf);
            border-left-color: var(--bs-primary);
            font-weight: 600;
        }

        .footer-custom {
            background-color: var(--bs-primary);
            color: var(--bs-snow);
            padding: 1rem 0;
            margin-top: auto;
        }
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
                        <li class="nav-item">
                            <span class="nav-link text-light me-2">{{ session('user.rol_name') }}</span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">Cerrar Sesion</button>
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
                    <a class="nav-link active" href="{{ route('admin.muebles.index') }}">Muebles</a>
                    <a class="nav-link" href="{{ route('admin.categorias.index') }}">Categorías</a>
                    <a class="nav-link" href="{{ route('admin.usuarios.index') }}">Usuarios</a>
                    <a class="nav-link" href="{{ route('admin.logs') }}">Logs de Actividad</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Gestion de Muebles</h1>

                @if (session('success'))
                    <div class="alert alert-success shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('admin.muebles.index') }}" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o descripcion..." value="{{ $search ?? '' }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Buscar</button>
                        </div>
                    </div>
                </form>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title text-primary">Listado de Muebles</h5>
                            <a href="{{ route('admin.muebles.create') }}" class="btn btn-primary">
                                Crear Nuevo Mueble
                            </a>
                        </div>

                        <div class="table-responsive mt-3">
                            @php
                                $apiFurnitureUrl = rtrim(env('API_FURNITURE_URL'), '/');
                            @endphp
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Imagen</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Stock</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($muebles as $mueble)
                                        <tr>
                                            <td>{{ $mueble->id }}</td>
                                            <td>
                                                @if (!empty($mueble->main_image))
                                                    <img src="{{ $apiFurnitureUrl }}/{{ $mueble->main_image }}"
                                                         alt="{{ $mueble->name }}"
                                                         style="width: 60px; height: 60px; object-fit: cover;"
                                                         class="border rounded">
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $mueble->name }}</td>
                                            <td>{{ number_format($mueble->price, 2) }} EUR</td>
                                            <td>{{ $mueble->stock }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('admin.muebles.show', ['mueble' => $mueble->id]) }}"
                                                        class="btn btn-sm btn-info text-white">
                                                        Ver
                                                    </a>

                                                    <a href="{{ route('admin.muebles.edit', ['mueble' => $mueble->id]) }}"
                                                        class="btn btn-sm btn-secondary">
                                                        Editar
                                                    </a>

                                                    <form action="{{ route('admin.muebles.destroy', ['mueble' => $mueble->id]) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Estas seguro de que quieres eliminar este mueble?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary">
                                                No hay muebles para mostrar.
                                            </td>
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

    <footer class="footer-custom text-center mt-auto">
        <div class="container">
            <p class="mb-0">&copy; {{ date('Y') }} Tienda de Muebles. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
