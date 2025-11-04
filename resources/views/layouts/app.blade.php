@php

    if (!isset($activeSesionId)) {
        $activeSesionId = request()->query('sesionId');
    }

    // Debemos definir $activeUser SIEMPRE,
    // independientemente de si las otras variables venían del controlador.
    if (!isset($activeUser)) {
        if ($activeSesionId) {
            $activeUser = \App\Models\User::activeUserSesion($activeSesionId);
        } else {
            $activeUser = null;
        }
    }

    // (Obtenemos las preferencias, ya que el controlador puede haberlas pre-definido)
    if (!isset($preferencias)) {
        $defaultPrefs = [
            'tema' => 'claro',
            'moneda' => 'EUR',
            'tamaño' => 6,
        ];

        // Usamos el $activeUser que acabamos de definir
        if ($activeUser) {
            $cookieName = 'preferencias_' . $activeUser->id;
            $cookieData = json_decode(request()->cookie($cookieName), true);

            if ($cookieData) {
                $preferencias = array_merge($defaultPrefs, $cookieData);
            } else {
                $preferencias = $defaultPrefs;
            }
        } else {
            // No hay sesión activa o el sesionId es inválido
            $preferencias = $defaultPrefs;
        }
    }
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

    <!-- CSS: Bootstrap PRIMERO -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS: Tus paletas DESPUÉS -->
    <link rel="stylesheet" href="{{ asset('css/paletas.css') }}">

</head>

<body>

    <nav class="navbar navbar-expand-lg shadow-sm" data-bs-theme="{{ $preferencias['tema'] ?? 'claro' }}">
        <div class="container">
            <a class="navbar-brand" href="{{ route('principal', ['sesionId' => $activeSesionId]) }}">Tienda Muebles
                JJDAY</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('categorias.index', ['sesionId' => $activeSesionId]) }}">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('muebles.index', ['sesionId' => $activeSesionId]) }}">Todos
                            los Muebles</a>
                    </li>

                    @if ($activeUser)
                        <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('carrito.show', ['sesionId' => $activeSesionId]) }}">Carrito</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('login.show', ['sesionId' => $activeSesionId]) }}">Carrito</a>
                        </li>
                    @endif

                    @if($activeUser)
                        <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('preferencias.show', ['sesionId' => $activeSesionId]) }}">Preferencias</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link"
                                href="{{ route('login.show', ['sesionId' => $activeSesionId]) }}">Preferencias</a>
                        </li>
                    @endif
                    @if ($activeUser)
                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $activeSesionId }}">
                                <button type="submit" class="btn btn-link nav-link">Logout
                                    ({{ $activeUser->email }})</button>
                            </form>
                        </li>
                    @else
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
