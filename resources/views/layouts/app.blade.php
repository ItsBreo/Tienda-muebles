@php
    // ------------------------------------------------------------------------
    // LÓGICA DE SESIÓN Y PREFERENCIAS (MODO DEPURACIÓN)
    // ------------------------------------------------------------------------

    // 1. Obtener ID de Sesión
    if (!isset($activeSesionId)) {
        $activeSesionId = request()->query('sesionId');
    }

    // --- CHIVATO 1: ¿Llega el ID? ---
    dump('1. ID en URL:', $activeSesionId);

    // 2. Obtener Usuario Activo
    if (!isset($activeUser)) {
        if ($activeSesionId) {
            // --- CHIVATO 2: ¿Qué hay en la sesión global? ---
            $todosLosUsuarios = \Illuminate\Support\Facades\Session::get('usuarios', []);
            dump('2. Array de Usuarios en Sesión:', $todosLosUsuarios);

            // --- CHIVATO 3: ¿Existe mi ID en ese array? ---
            $datosUsuarioJson = $todosLosUsuarios[$activeSesionId] ?? null;
            dump('3. Datos JSON para mi ID:', $datosUsuarioJson);

            if ($datosUsuarioJson) {
                // Intentamos buscar en BD
                $datos = json_decode($datosUsuarioJson);
                dump('4. Buscando en BD el ID:', $datos->id);
                $activeUser = \App\Models\User::activeUserSesion($activeSesionId);
                dump('5. Resultado User final:', $activeUser);
            } else {
                dump('FALLO: El sesionId de la URL no existe en el array de sesión.');
                $activeUser = null;
            }
        } else {
            $activeUser = null;
        }
    }

    // 3. Obtener Preferencias
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
            $preferencias = $defaultPrefs;
        }
    }

    dump('6. Preferencias:', $preferencias);

    $temaActual = $preferencias['tema'] ?? 'claro';
    $bsTheme = ($temaActual === 'oscuro') ? 'dark' : 'light';
    $navbarClass = ($temaActual === 'oscuro') ? 'navbar-dark' : 'navbar-light';

@endphp
<!DOCTYPE html>
<html lang="es" data-theme="{{ $temaActual }}">
<!-- ... el resto de tu archivo sigue igual ... -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Muebles - @yield('title', 'Inicio')</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/paletas.css') }}">
</head>

<body data-bs-theme="{{ $bsTheme }}">

    <nav class="navbar navbar-expand-lg shadow-sm {{ $navbarClass }}" data-bs-theme="{{ $bsTheme }}">
        <div class="container">

            <a class="navbar-brand custom-brand-centered"
               href="{{ route('principal', ['sesionId' => $activeSesionId]) }}">
                <span class="brand-text">Tienda Muebles</span>
                <img src="{{ asset('images/JJDAY.png') }}" alt="Logo Tienda Muebles JJDAY" class="navbar-logo" style="width: 100px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    {{-- Enlaces públicos con sesionId --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('categorias.index', ['sesionId' => $activeSesionId]) }}">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('muebles.index', ['sesionId' => $activeSesionId]) }}">Todos los Muebles</a>
                    </li>

                    {{-- !! CORRECCIÓN: Usamos $activeUser aquí !! --}}
                    @if ($activeUser)

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('carrito.show', ['sesionId' => $activeSesionId]) }}">Carrito</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('preferencias.show', ['sesionId' => $activeSesionId]) }}">Preferencias</a>
                        </li>

                        {{-- Comprobamos rol sobre $activeUser --}}
                        @if ($activeUser->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link fw-bold" href="{{ route('admin.dashboard', ['sesionId' => $activeSesionId]) }}">Administración</a>
                            </li>
                        @endif

                        <li class="nav-item ms-2">
                            <form action="{{ route('login.logout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $activeSesionId }}">
                                <button type="submit" class="btn btn-link nav-link">
                                    Logout ({{ $activeUser->email }})
                                </button>
                            </form>
                        </li>
                    @else
                        {{-- Si no hay usuario activo --}}
                        <li class="nav-item ms-2">
                            <a class="nav-link btn btn-outline-primary px-3" href="{{ route('login.show') }}">Login</a>
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
        <div class="container">
            <p class="mb-0 text-muted">&copy; {{ date('Y') }} Tienda de Muebles JJDAY. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
