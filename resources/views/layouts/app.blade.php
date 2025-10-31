<!DOCTYPE html>
{{-- Esta será la plantilla principal para toda la web --}}
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Muebles - @yield('title', 'Inicio')</title>

    <!-- Google Fonts: Inter + Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #7A7D7D; /* Gray */
            background-color: #FFFFFF; /* Blanco Puro */
        }
        h1, h2, h3, h4, h5, h6, .navbar-brand, .card-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 400; /* Regular */
            color: #565254; /* Davy's Gray */
        }
        .navbar {
            background-color: #FFFBFE !important; /* Snow (Blanco Casi Puro) */
        }
        .btn-primary {
            background-color: #565254; /* Davy's Gray */
            border-color: #565254;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #3d3b3c;
            border-color: #3d3b3c;
        }
        .btn-outline-secondary {
            color: #565254;
            border-color: #565254;
        }
        .btn-outline-secondary:hover {
            background-color: #565254;
            color: #FFFFFF;
        }
        .page-item.active .page-link {
            background-color: #565254;
            border-color: #565254;
        }
        .page-link {
            color: #565254;
        }
        /* Estilo para que el botón de Logout parezca un link */
        .btn-link-nav {
            color: var(--bs-nav-link-color);
            padding: var(--bs-nav-link-padding-y) var(--bs-nav-link-padding-x);
            font-weight: var(--bs-nav-link-font-weight);
            text-decoration: none;
            border: none;
            background: none;
        }
        .btn-link-nav:hover {
            color: var(--bs-nav-link-hover-color);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('principal') }}">Tienda Muebles JJDAY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('categorias.index') }}">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('muebles.index') }}">Todos los Muebles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('carrito.show') }}">Carrito</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('preferencias.show') }}">Preferencias</a>
                    </li>


                    @if(Session::has('current_sesion_id'))
                        {{-- Si 'current_sesion_id' existe, el usuario está logueado --}}
                        <li class="nav-item">
                            {{-- Apunta a la ruta que llama al método 'logout' --}}
                            <form action="{{ route('login.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-link-nav">Logout</button>
                            </form>
                        </li>
                    @else
                        {{-- Si no, mostramos el enlace de Login --}}
                        <li class="nav-item">
                            {{-- CORREGIDO: Usamos el nombre de ruta 'login.show' --}}
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        @yield('content')
    </main>

    <footer class="text-center py-4 mt-auto" style="background-color: #FFFBFE; border-top: 1px solid #f0f0f0;">
        <p class="mb-0 text-muted">&copy; {{ date('Y') }} Tienda de Muebles JJDAY. Todos los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
