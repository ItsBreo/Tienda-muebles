<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mueble - Tienda</title>

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
                <h1 class="mb-4 text-primary">Editar Mueble: {{ $mueble->getName() }}</h1>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Detalles del Mueble</h5>
                        <hr>
                        <form action="{{ route('admin.muebles.update', $mueble->getId()) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('POST')

                            {{-- Añadido sesionId para que el update no falle el checkAdmin --}}
                            <input type="hidden" name="sesionId" value="{{ $sesionId }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nombre del Mueble</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $mueble->getName()) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="price" class="form-label">Precio</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price', $mueble->getPrice()) }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock', $mueble->getStock()) }}" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $mueble->getDescription()) }}</textarea>
                                </div>


                                <div class="col-md-4">
                                    <label for="category_id" class="form-label">Categoría</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Seleccione una categoría...</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->getId() }}"
                                                {{-- Comparamos old() con el ID actual del mueble --}}
                                                @if(old('category_id', $mueble->getCategoryId()) == $category->getId()) selected @endif>
                                                {{ $category->getName() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="materials" class="form-label">Materiales</label>
                                    <input type="text" class="form-control" id="materials" name="materials" value="{{ old('materials', $mueble->getMaterials()) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="dimensions" class="form-label">Dimensiones</label>
                                    <input type="text" class="form-control" id="dimensions" name="dimensions" value="{{ old('dimensions', $mueble->getDimensions()) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="main_color" class="form-label">Color Principal</label>
                                    <input type="text" class="form-control" id="main_color" name="main_color" value="{{ old('main_color', $mueble->getMainColor()) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Imagen Principal</label>
                                    <input type="file" class="form-control" id="image" name="image">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        {{-- El value="1" es importante para que se envíe algo --}}
                                        <input class="form-check-input" type="checkbox" id="is_salient" name="is_salient" value="1"
                                            {{-- Comprobamos old() o el estado actual del mueble --}}
                                            @if(old('is_salient', $mueble->isSalient())) checked @endif>
                                        <label class="form-check-label" for="is_salient">¿Es un producto destacado?</label>
                                    </div>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary">Actualizar Mueble</button>

                                    <a href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                {{-- TODO: Corregir la subida de imagenes --}}
                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Galería de Imágenes</h5>
                        <hr>

                        <form action="{{ route('productos.galeria.store', ['mueble' => $mueble->getId()]) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                            @csrf

                            <input type="hidden" name="sesionId" value="{{ $sesionId }}">

                            <div class="mb-3">
                                <label for="images" class="form-label">Añadir nuevas imágenes</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple required>
                            </div>
                            <button type="submit" class="btn btn-primary">Subir Imágenes</button>
                        </form>

                        {{-- TODO: Mostrar imágenes existentes funciona correactmente aqui pero en la vista no --}}
                        @if (count($mueble->getImages()) > 0 && $mueble->getImages() !== ['default.jpg'])
                            <div class="row g-3">
                                @foreach ($mueble->getImages() as $image)
                                    @if ($image !== 'default.jpg')
                                        <div class="col-md-3">
                                            <div class="card">
                                                <img src="{{ asset($image) }}" class="card-img-top" alt="Imagen del mueble" style="height: 150px; object-fit: cover;">
                                                <div class="card-body text-center">


                                                    <form action="{{ route('productos.galeria.destroy', ['mueble' => $mueble->getId(), 'image' => basename($image)]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')

                                                        <input type="hidden" name="sesionId" value="{{ $sesionId }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar imagen" onclick="return confirm('¿Seguro que quieres eliminar esta imagen?');">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
