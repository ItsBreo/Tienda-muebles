<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Mueble - Tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Paleta */
        :root {
            --bs-davys-gray: #565254;
            --bs-gray-medium: #7A7D7D;
            --bs-timberwolf: #D0CFCF;
            --bs-snow: #FFFBFE;
            --bs-primary: var(--bs-davys-gray); /* Color principal: Gris Oscuro */
            --bs-secondary: var(--bs-gray-medium); /* Color secundario: Gris Medio */
        }

        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bs-timberwolf); /* Fondo de página gris claro */
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
        .navbar-custom .nav-link {
            color: var(--bs-snow) !important;
        }

        .sidebar {
            width: 250px;
            background-color: var(--bs-secondary); /* Gris Medio */
            padding-top: 1rem;
            min-height: calc(100vh - 56px); /* 100vh menos altura del navbar */
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
            background-color: var(--bs-primary); /* Gris Oscuro */
            color: var(--bs-snow);
            padding: 1rem 0;
            margin-top: auto; /* Empuja el footer hacia abajo */
        }
        .detail-label {
            font-weight: 600;
            color: var(--bs-secondary);
        }
    </style>
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
            <div class="container-fluid">

                <a class="navbar-brand fw-bold" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Panel de Control</a>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#">Usuario (Admin)</a>
                        </li>
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

            <div class="col-md-3 col-lg-2 sidebar">
                <div class="nav flex-column nav-pills">

                    <a class="nav-link" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Dashboard</a>
                    <a class="nav-link" href="#">Usuarios</a>
                    <a class="nav-link active" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Muebles</a>
                    <a class="nav-link" href="#">Configuración</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Detalle del Mueble</h1>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title text-primary mb-0">{{ $mueble->getName() }}</h5>
                            <div>

                                <a href="{{ route('admin.muebles.edit', ['id' => $mueble->getId(), 'sesionId' => $sesionId]) }}" class="btn btn-secondary">Editar</a>
                                <a href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}" class="btn btn-outline-secondary">Volver al listado</a>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-3">
                            <div class="col-md-6"><p><span class="detail-label">ID:</span> {{ $mueble->getId() }}</p></div>
                            <div class="col-md-6"><p><span class="detail-label">Categoría ID:</span> {{ $mueble->getCategoryId() }}</p></div>
                            <div class="col-md-6"><p><span class="detail-label">Precio:</span> {{ number_format($mueble->getPrice(), 2) }} €</p></div>
                            <div class="col-md-6"><p><span class="detail-label">Stock:</span> {{ $mueble->getStock() }}</p></div>
                            <div class="col-12"><p><span class="detail-label">Descripción:</span><br>{{ $mueble->getDescription() }}</p></div>
                            <div class="col-md-6"><p><span class="detail-label">Materiales:</span> {{ $mueble->getMaterials() ?: 'No especificado' }}</p></div>
                            <div class="col-md-6"><p><span class="detail-label">Dimensiones:</span> {{ $mueble->getDimensions() ?: 'No especificado' }}</p></div>
                            <div class="col-md-6"><p><span class="detail-label">Color Principal:</span> {{ $mueble->getMainColor() }}</p></div>
                            <div class="col-md-6"><p><span class="detail-label">Destacado:</span> {{ $mueble->isSalient() ? 'Sí' : 'No' }}</p></div>
                            <div class="col-12">
                                <p class="detail-label">Imágenes:</p>
                                @forelse ($mueble->getImages() as $image)
                                    <span class="badge bg-light text-dark">{{ $image }}</span>
                                @empty
                                    <span class="text-muted">No hay imágenes asociadas.</span>
                                @endforelse
                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
