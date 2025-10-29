<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Empresa</title>

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

        /* 1. Fuente de Letra (Ejemplo de fuente profesional y limpia) */
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--bs-timberwolf); /* Fondo de página gris claro */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* 2. Cabecera (Navbar) */
        .navbar-custom {
            background-color: var(--bs-primary) !important;
            color: var(--bs-snow);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: var(--bs-snow) !important;
        }
        .navbar-custom .nav-link:hover {
            color: var(--bs-timberwolf) !important;
        }

        /* 3. Barra Lateral (Sidebar) */
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

        /* 4. Pie de Página (Footer) */
        .footer-custom {
            background-color: var(--bs-primary); /* Gris Oscuro */
            color: var(--bs-snow);
            padding: 1rem 0;
            margin-top: auto; /* Empuja el footer hacia abajo */
            box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="#">Panel de Control</a>

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
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active" id="v-pills-dashboard-tab" data-bs-toggle="pill" href="#v-pills-dashboard" role="tab" aria-selected="true">Dashboard</a>
                    <a class="nav-link" id="v-pills-usuarios-tab" data-bs-toggle="pill" href="#v-pills-usuarios" role="tab" aria-selected="false">Usuarios</a>
                    <a class="nav-link" id="v-pills-productos-tab" data-bs-toggle="pill" href="#v-pills-productos" role="tab" aria-selected="false">Productos</a>
                    <a class="nav-link" id="v-pills-config-tab" data-bs-toggle="pill" href="#v-pills-config" role="tab" aria-selected="false">Configuración</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h1 class="mb-4 text-primary">Dashboard Principal</h1>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Resumen Rápido</h5>
                        <p class="card-text text-secondary">
                            Aquí puedes colocar métricas, gráficos o la información más importante.
                        </p>
                        <button class="btn btn-primary">Ver Reporte</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h5 class="card-title text-secondary">Últimas Actividades</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Usuario X se registró.</li>
                                    <li class="list-group-item">Producto Y editado.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="footer-custom text-center mt-auto">
        <div class="container">
            <p class="mb-0">
                &copy; {{ date('Y') }} Nombre de tu Empresa. Todos los derechos reservados.
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
