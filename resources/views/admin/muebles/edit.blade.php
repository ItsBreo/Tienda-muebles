<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mueble - Tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

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
    </style>
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="{{ route('admin.muebles.index') }}">Panel de Control</a>
                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="#">Usuario (Admin)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Cerrar Sesión</a>
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
                    <a class="nav-link" href="#">Dashboard</a>
                    <a class="nav-link" href="#">Usuarios</a>
                    <a class="nav-link active" href="{{ route('admin.muebles.index') }}">Muebles</a>
                    <a class="nav-link" href="#">Configuración</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Editar Mueble: {{ $mueble->getName() }}</h1>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Detalles del Mueble</h5>
                        <hr>
                        <form action="{{ route('admin.muebles.update', $mueble->getId()) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nombre del Mueble</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $mueble->getName() }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="price" class="form-label">Precio</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ $mueble->getPrice() }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" value="{{ $mueble->getStock() }}" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required>{{ $mueble->getDescription() }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label for="category_id" class="form-label">ID de Categoría</label>
                                    <input type="number" class="form-control" id="category_id" name="category_id" value="{{ $mueble->getCategoryId() }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="materials" class="form-label">Materiales</label>
                                    <input type="text" class="form-control" id="materials" name="materials" value="{{ $mueble->getMaterials() }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="dimensions" class="form-label">Dimensiones</label>
                                    <input type="text" class="form-control" id="dimensions" name="dimensions" value="{{ $mueble->getDimensions() }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="main_color" class="form-label">Color Principal</label>
                                    <input type="text" class="form-control" id="main_color" name="main_color" value="{{ $mueble->getMainColor() }}" required>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_salient" name="is_salient" value="1" @if($mueble->isSalient()) checked @endif>
                                        <label class="form-check-label" for="is_salient">¿Es un producto destacado?</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary">Actualizar Mueble</button>
                                    <a href="{{ route('admin.muebles.index') }}" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Sección para la Galería de Imágenes --}}
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Galería de Imágenes</h5>
                        <hr>

                        {{-- Formulario para subir nuevas imágenes --}}
                        <form action="{{ route('productos.galeria.store', ['mueble' => $mueble->getId()]) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <label for="images" class="form-label">Añadir nuevas imágenes</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple required>
                            </div>
                            <button type="submit" class="btn btn-primary">Subir Imágenes</button>
                        </form>

                        {{-- Mostrar imágenes existentes --}}
                        @if (count($mueble->getImages()) > 0 && $mueble->getImages() !== ['default.jpg'])
                            <div class="row g-3">
                                @foreach ($mueble->getImages() as $image)
                                    @if ($image !== 'default.jpg')
                                        <div class="col-md-3">
                                            <div class="card">
                                                <img src="{{ asset('images/' . $image) }}" class="card-img-top" alt="Imagen del mueble" style="height: 150px; object-fit: cover;">
                                                <div class="card-body text-center">
                                                    {{-- Formulario para eliminar imagen --}}
                                                    <form action="{{ route('productos.galeria.destroy', ['mueble' => $mueble->getId(), 'image' => $image]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar imagen">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                Este mueble aún no tiene imágenes en su galería.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
