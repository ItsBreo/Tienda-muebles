<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mueble - Tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">

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

        .gallery-card {
            height: 100%;
            display: flex;
            flex-direction: column;
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
                            <span class="nav-link">{{ session('user.rol_name') }}</span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link" style="text-decoration: none;">
                                    Cerrar Sesion
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
                    <a class="nav-link" href="{{ route('principal') }}">Ir a la tienda</a>
                    <a class="nav-link active" href="{{ route('admin.muebles.index') }}">Muebles</a>
                    <a class="nav-link" href="{{ route('admin.categorias.index') }}">Categorías</a>
                    <a class="nav-link" href="{{ route('admin.usuarios.index') }}">Usuarios</a>
                    <a class="nav-link" href="{{ route('admin.logs') }}">Logs de Actividad</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Editar Mueble: {{ $mueble->name }}</h1>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

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
                        <h5 class="card-title text-primary">Detalles del Mueble</h5>
                        <hr>

                        <form action="{{ route('admin.muebles.update', ['mueble' => $mueble->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nombre del Mueble</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name', $mueble->name) }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="price" class="form-label">Precio</label>
                                    <input type="number" step="0.01" class="form-control" id="price"
                                        name="price" value="{{ old('price', $mueble->price) }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock"
                                        value="{{ old('stock', $mueble->stock) }}" required>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Descripcion</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $mueble->description) }}</textarea>
                                </div>

                                <div class="col-md-4">
                                    <label for="category_id" class="form-label">Categoria</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Seleccione una categoria...</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" @if (old('category_id', $mueble->category_id) == $category->id) selected @endif>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="materials" class="form-label">Materiales</label>
                                    <input type="text" class="form-control" id="materials" name="materials"
                                        value="{{ old('materials', $mueble->materials) }}">
                                </div>

                                <div class="col-md-4">
                                    <label for="dimensions" class="form-label">Dimensiones</label>
                                    <input type="text" class="form-control" id="dimensions" name="dimensions"
                                        value="{{ old('dimensions', $mueble->dimensions) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="main_color" class="form-label">Color Principal</label>
                                    <input type="text" class="form-control" id="main_color" name="main_color"
                                        value="{{ old('main_color', $mueble->main_color) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="image" class="form-label">Actualizar Imagen Principal (Opcional)</label>
                                    <input type="file" class="form-control" id="image" name="image">
                                </div>

                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_salient"
                                            name="is_salient" value="1"
                                            @if (old('is_salient', $mueble->is_salient)) checked @endif>
                                        <label class="form-check-label" for="is_salient">Es un producto destacado</label>
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

                <div class="card shadow-sm border-0 mt-4 mb-5">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Galeria de Imagenes</h5>
                        <hr>

                        @if (collect($mueble->images ?? [])->isNotEmpty())
                            <div class="row g-3">
                                @foreach ($mueble->images as $image)
                                    <div class="col-md-3">
                                        <div class="card gallery-card">
                                            <img src="{{ asset($image->image_path) }}" class="card-img-top"
                                                alt="Imagen galeria" style="height: 150px; object-fit: cover;">

                                            <div class="card-body text-center d-flex align-items-end justify-content-center">
                                                @if($image->is_primary)
                                                    <span class="badge bg-success">Principal</span>
                                                @else
                                                    <span class="badge bg-secondary">Galeria</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info mt-3">
                                Este mueble aun no tiene imagenes adicionales en su galeria.
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
        crossorigin="anonymous"></script>
</body>

</html>
