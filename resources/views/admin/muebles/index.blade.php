<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Muebles - Tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Paleta */
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

        .navbar-custom .btn-link:hover {
            color: var(--bs-timberwolf) !important;
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

                {{-- CAMBIO 1: Agregado parámetro sesionId --}}
                <a class="navbar-brand fw-bold" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">
                    Panel de Control
                </a>

                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            {{-- CAMBIO 2: Agregado input hidden sesionId --}}
                            <form action="{{ route('login.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $sesionId }}">
                                <button type="submit" class="btn btn-link nav-link">Cerrar Sesión</button>
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
                    <a class="nav-link" href="{{ route('admin.usuarios.index', ['sesionId' => $sesionId]) }}">Usuarios</a>
                    <a class="nav-link active" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Muebles</a>
                    <a class="nav-link" href="{{ route('admin.categorias.index', ['sesionId' => $sesionId]) }}">Categorias</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Gestión de Muebles</h1>

                @if (session('success'))
                    <div class="alert alert-success shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title text-primary">Listado de Muebles</h5>

                            {{-- CAMBIO 4: Agregado sesionId del botón crear --}}
                            <a href="{{ route('admin.muebles.create', ['sesionId' => $sesionId]) }}" class="btn btn-primary">
                                Crear Nuevo Mueble
                            </a>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
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
                                            <td>{{ $mueble->name }}</td>
                                            <td>{{ number_format($mueble->price, 2) }} €</td>
                                            <td>{{ $mueble->stock }}</td>

                                            <td>
                                                <div class="d-flex gap-1">
                                                    {{-- CAMBIO 5: Agregado sesionId --}}
                                                    <a href="{{ route('admin.muebles.show', ['sesionId' => $sesionId, 'mueble' => $mueble->id]) }}"
                                                        class="btn btn-sm btn-info text-white">
                                                        Ver
                                                    </a>

                                                    <a href="{{ route('admin.muebles.edit', ['sesionId' => $sesionId, 'mueble' => $mueble->id]) }}"
                                                        class="btn btn-sm btn-secondary">
                                                        Editar
                                                    </a>

                                                    <form action="{{ route('admin.muebles.destroy', ['sesionId' => $sesionId, 'mueble' => $mueble->id]) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('¿Estás seguro de que quieres eliminar este mueble?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-secondary">
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
