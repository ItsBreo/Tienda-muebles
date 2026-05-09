<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Actividad - Tienda</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Paleta */
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
            background-color: var(--bs-primary);
        }

        .sidebar {
            background-color: var(--bs-primary);
            min-height: calc(100vh - 56px);
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

                <a class="navbar-brand fw-bold" href="{{ route('admin.logs', ['sesionId' => $sesionId ?? request('sesionId')]) }}">
                    Panel de Control
                </a>

                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <form action="{{ route('login.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="sesionId" value="{{ $sesionId ?? request('sesionId') }}">
                                <button type="submit" class="btn btn-link nav-link">Cerrar Sesión</button>
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
                    <a class="nav-link" href="{{ route('principal', ['sesionId' => $sesionId ?? request('sesionId')]) }}">Ir a la tienda</a>
                    <a class="nav-link" href="{{ route('admin.usuarios.index', ['sesionId' => $sesionId ?? request('sesionId')]) }}">Usuarios</a>
                    <a class="nav-link" href="{{ route('admin.muebles.index', ['sesionId' => $sesionId ?? request('sesionId')]) }}">Muebles</a>
                    <a class="nav-link" href="{{ route('admin.categorias.index', ['sesionId' => $sesionId ?? request('sesionId')]) }}">Categorias</a>
                    <a class="nav-link active" href="{{ route('admin.logs', ['sesionId' => $sesionId ?? request('sesionId')]) }}">📋 Logs de Actividad</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Logs de Actividad del Sistema</h1>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>
                            Registro de Actividades
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($logs && isset($logs['data']))
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Usuario</th>
                                            <th>Acción</th>
                                            <th>Método</th>
                                            <th>URL</th>
                                            <th>Dirección IP</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($logs['data'] as $log)
                                            <tr>
                                                <td>
                                                    @if($log->user_id)
                                                        <span class="badge bg-info text-white">
                                                            {{ $log->user_id }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary text-white">
                                                            Sistema
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary text-white">
                                                        {{ $log->action }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($log->method === 'POST')
                                                        <span class="badge bg-success text-white">POST</span>
                                                    @elseif($log->method === 'PUT')
                                                        <span class="badge bg-warning text-dark">PUT</span>
                                                    @elseif($log->method === 'DELETE')
                                                        <span class="badge bg-danger text-white">DELETE</span>
                                                    @else
                                                        <span class="badge bg-secondary text-white">{{ $log->method }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $log->url }}</small>
                                                </td>
                                                <td>
                                                    @if($log->ip_address)
                                                        <code class="text-muted">{{ $log->ip_address }}</code>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Paginación --}}
                            @if(isset($logs['links']) && $logs['links'])
                                <div class="d-flex justify-content-center mt-4">
                                    {!! $logs['links'] !!}
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay registros de actividad disponibles.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer-custom text-center">
        <div class="container">
            <p class="mb-0">&copy; 2024 Tienda de Muebles. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>
