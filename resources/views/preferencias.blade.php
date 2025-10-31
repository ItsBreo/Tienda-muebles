<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Preferencias</title>

    <!-- Cargar Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Un pequeño estilo para centrar el contenido verticalmente */
        body, html {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa; /* bg-light */
        }
    </style>
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-sm-9">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-4 p-md-5">

                        <h1 class="h3 fw-bold text-center text-dark mb-3">
                            Configura tus Preferencias
                        </h1>
                        <p class="text-center text-muted mb-4">
                            Elige tus preferencias iniciales. Podrás cambiarlas más tarde.
                        </p>

                        <!--
                          El 'action' apunta a la ruta 'preferencias.store'
                          que definimos en web.php
                        -->
                        <form action="{{ route('preferencias.update') }}" method="POST">
                            <!-- Token de seguridad de Laravel -->
                            @csrf

                            <!-- Desplegable de Tema -->
                            <div class="mb-3">
                                <label for="tema" class="form-label">
                                    Tema de la Aplicación
                                </label>
                                <select id="tema" name="tema" class="form-select" required>
                                    <option value="" disabled selected>Selecciona un tema...</option>
                                    <option value="claro">Claro</option>
                                    <option value="oscuro">Oscuro</option>
                                    <option value="sistema">Usar tema del sistema</option>
                                </select>
                            </div>

                            <!-- Desplegable de Moneda -->
                            <div class="mb-3">
                                <label for="moneda" class="form-label">
                                    Moneda Principal
                                </label>
                                <select id="moneda" name="moneda" class="form-select" required>
                                    <option value="" disabled selected>Selecciona una moneda...</option>
                                    <option value="USD">USD - Dólar Americano</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="MXN">MXN - Peso Mexicano</option>
                                    <option value="COP">COP - Peso Colombiano</option>
                                    <option value="ARS">ARS - Peso Argentino</option>
                                </select>
                            </div>

                            <!-- --- CORREGIDO --- -->
                            <!-- Desplegable de Tamaño (Productos por Fila) -->
                            <div class="mb-3">
                                <label for="tamaño" class="form-label">
                                    Productos por Fila
                                </label>
                                <select id="tamaño" name="tamaño" class="form-select" required>
                                    <option value="" disabled selected>Selecciona una cantidad...</option>
                                    <!-- Los 'value' son números, como espera la validación -->
                                    <option value="2">2 Productos</option>
                                    <option value="3">3 Productos (Default)</option>
                                    <option value="4">4 Productos</option>
                                    <option value="6">6 Productos</option>
                                </select>
                            </div>

                            <!-- Botón de Enviar -->
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Guardar Preferencias
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cargar Bootstrap JS (Bundle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
