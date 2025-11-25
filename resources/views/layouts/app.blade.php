@php
    // El usuario activo se obtiene directamente del sistema de autenticación de Laravel.
    // La variable $activeUser ya no es necesaria, usaremos auth()->user() directamente.
    // La variable $activeSesionId queda obsoleta y ya no se usa.

    // Lógica para las preferencias.
    // Si un controlador ya las ha definido, se usan esas. Si no, las calculamos aquí.
    if (!isset($preferencias)) {
        $defaultPrefs = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        // Si hay un usuario autenticado, intentamos leer su cookie.
        if (auth()->check()) {
            $cookieName = 'preferencias_' . auth()->user()->id;
            $cookieData = json_decode(request()->cookie($cookieName), true);

            if ($cookieData) {
                $preferencias = array_merge($defaultPrefs, $cookieData);
            } else {
                $preferencias = $defaultPrefs;
            }
        } else {
            // Si no hay usuario, usamos las preferencias por defecto.
            $preferencias = $defaultPrefs;
        }
    }

    $bsTheme = $preferencias['tema'] === 'oscuro' ? 'dark' : 'light';
    $navbarClass = $preferencias['tema'] === 'oscuro' ? 'navbar-dark' : 'navbar-light';
@endphp
<!DOCTYPE html>
<html lang="es" data-theme="{{ $preferencias['tema'] ?? 'claro' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Muebles - @yield('title', 'Inicio')</title>

    <!-- Google Fonts: Inter + Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Poppins:wght@300;500;700&display=swap"
        rel="stylesheet">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/paletas.css') }}">

</head>

<body data-bs-theme="{{ $bsTheme }}">


    <nav class="navbar navbar-expand-lg shadow-sm {{ $navbarClass }}" data-bs-theme="{{ $bsTheme }}">
        <div class="container">

            {{-- Código Modificado para Centrar con Clases --}}
            <a class="navbar-brand custom-brand-centered"
                href="{{ route('principal') }}">
                <span class="brand-text">Tienda Muebles</span>
                <img src="{{ asset('images/JJDAY.png') }}" alt="Logo Tienda Muebles JJDAY" class="navbar-logo"
                    style="width: 100px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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

                    @auth
                        {{-- Si el usuario está logueado, muestra todos los enlaces --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('carrito.show') }}">Carrito</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('preferencias.show') }}">Preferencias</a>
                        </li>

                        {{-- También comprobamos el rol de admin aquí --}}
                        @if (auth()->user()->hasRole('Admin'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.muebles.index') }}">Administración</a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">Logout
                                    ({{ auth()->user()->email }})</button>
                            </form>
                        </li>
                    @else
                        {{-- Si no está logueado, solo muestra "Login" --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login.show') }}">Login</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        @yield('content')
    </main>

    <footer class="text-center py-4 mt-auto shadow-inner" style="background-color: var(--bs-tertiary-bg);">
        <p class="mb-0 text-muted">&copy; {{ date('Y') }} Tienda de Muebles JJDAY. Todos los derechos reservados.
        </p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>

</html>
