@php
    // Lógica de sesión por pestaña:
    // Las variables $user y $sesionId son pasadas por los controladores.
    // Si no existen, significa que el usuario no ha iniciado sesión en esta pestaña.

    // Lógica para las preferencias.
    if (!isset($preferencias)) {
        $defaultPrefs = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 4, // Valor por defecto consistente
        ];

        // En lugar de auth()->check(), comprobamos si la variable $user existe.
        if (isset($user) && $user) {
            $cookieName = 'preferencias_' . $user->id;
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
            {{-- El enlace principal también debe llevar el sesionId si existe --}}
            <a class="navbar-brand custom-brand-centered"
                href="{{ route('principal', ['sesionId' => $sesionId ?? null]) }}">
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
                    {{-- Todos los enlaces de navegación deben incluir el sesionId para mantener la sesión --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('categorias.index', ['sesionId' => $sesionId]) }}">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('muebles.index', ['sesionId' => $sesionId]) }}">Todos los Muebles</a>
                    </li>

                    {{-- Reemplazamos @auth con una comprobación de la variable $user --}}
                    @if (isset($user) && $user)
                        {{-- Si el usuario existe para esta pestaña, muestra sus enlaces --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('carrito.show', ['sesionId' => $sesionId]) }}">Carrito</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('preferencias.show', ['sesionId' => $sesionId]) }}">Preferencias</a>
                        </li>

                        {{-- Comprobamos el rol sobre el objeto $user --}}
                        @if ($user->hasRole('Admin'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId]) }}">Administración</a>
                            </li>
                        @endif

                        <li class="nav-item">
                            {{-- El formulario de logout debe enviar el sesionId para cerrar la sesión correcta --}}
                            <form action="{{ route('login.logout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $sesionId }}">
                                <button type="submit" class="btn btn-link nav-link">Logout
                                    ({{ $user->email }})</button>
                            </form>
                        </li>
                    @else
                        {{-- Si no hay usuario en esta pestaña, solo muestra "Login" --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login.show') }}">Login</a>
                        </li>
                    @endif {{-- Fin de la comprobación de $user --}}
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
