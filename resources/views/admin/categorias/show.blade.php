<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Categoría - Tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Paleta idéntica al resto */
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
                <a class="navbar-brand fw-bold" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">
                    Panel de Control
                </a>

                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <span class="nav-link">Usuario (Admin)</span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $sesionId }}">
                                <button type="submit" class="btn btn-link nav-link p-2" style="text-decoration: none;">
                                    Cerrar Sesión
                                </button>
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
                    <a class="nav-link" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Muebles</a>
                    <a class="nav-link active" href="{{ route('admin.categorias.index', ['sesionId' => $sesionId]) }}">Categorías</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">

                {{-- Título y Botones Superiores --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-primary mb-0">Detalle de Categoría</h1>
                    <div>
                        <a href="{{ route('admin.categorias.edit', $categoria) }}" class="btn btn-secondary">
                            Editar Categoría
                        </a>
                        <a href="{{ route('admin.categorias.index') }}" class="btn btn-outline-secondary">
                            Volver al Listado
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Información General</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted">Nombre:</label>
                                <p class="fs-5">{{ $categoria->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold text-muted">ID:</label>
                                <p>#{{ $categoria->id }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="fw-bold text-muted">Descripción:</label>
                                <p class="bg-light p-3 rounded border">
                                    {{ $categoria->description }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Creado el: {{ $categoria->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Última actualización: {{ $categoria->updated_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Muebles en esta Categoría</h5>
                        <hr>

                        {{-- Comprobamos si la categoría tiene muebles usando la relación --}}
                        {{-- NOTA: Asumo que en tu modelo Category la relación se llama 'furniture' o 'muebles' --}}
                        @if ($categoria->furniture->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Mueble</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($categoria->furniture as $mueble)
                                            <tr>
                                                <td>{{ $mueble->id }}</td>
                                                <td class="fw-bold">{{ $mueble->name }}</td>
                                                <td>{{ number_format($mueble->price, 2) }} €</td>
                                                <td>
                                                    @if($mueble->stock > 0)
                                                        <span class="badge bg-success">{{ $mueble->stock }} unid.</span>
                                                    @else
                                                        <span class="badge bg-danger">Agotado</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    {{-- Botón para ir a ver ese mueble específico --}}
                                                    <a href="{{ route('admin.muebles.show', $mueble) }}"
                                                       class="btn btn-sm btn-info text-white">
                                                        Ver Mueble
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No hay muebles registrados en esta categoría actualmente.
                            </div>
                        @endif

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
