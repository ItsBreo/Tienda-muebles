<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login con Paleta de Colores</title>

    {{-- Asumiendo que bootstrap.min.css está en public/resources/ --}}
    <link rel="stylesheet" href="{{ asset('resources/bootstrap.min.css') }}">

    <style>
        body {
            background-color: #D0CFCF; /* Timberwolf - Fondo general claro */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; /* Fuente más moderna */
        }
        .card {
            min-width: 400px;
            border-radius: 0.75rem; /* Bordes más redondeados */
            overflow: hidden; /* Asegura que los bordes redondeados se apliquen al header */
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); /* Sombra más pronunciada */
            border: none; /* Quitamos el borde por defecto de Bootstrap */
        }
        .card-header {
            background-color: #565254; /* Davy's Gray - Encabezado oscuro */
            color: #FFFFFF; /* Texto blanco para contraste */
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .card-body {
            padding: 2.5rem;
            background-color: #FFFBFE; /* Snow - Fondo del cuerpo de la tarjeta */
        }
        .form-label {
            color: #7A7D7D; /* Gray - Etiquetas de los campos */
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border: 1px solid #7A7D7D; /* Borde gris para los inputs */
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            color: #565254; /* Texto de input oscuro */
        }
        .form-control:focus {
            border-color: #565254; /* Borde oscuro al enfocar */
            box-shadow: 0 0 0 0.25rem rgba(86, 82, 84, 0.25); /* Sombra de enfoque */
        }
        .btn-primary {
            background-color: #565254; /* Davy's Gray - Botón oscuro */
            border-color: #565254; /* Borde del botón oscuro */
            color: #FFFFFF; /* Texto del botón blanco */
            padding: 0.8rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #7A7D7D; /* Gris medio al pasar el ratón */
            border-color: #7A7D7D;
            color: #FFFFFF;
        }
        .form-check-input:checked {
            background-color: #565254;
            border-color: #565254;
        }
        .form-check-label {
            color: #7A7D7D; /* Gray - Etiqueta del checkbox */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card">
                    <div class="card-header text-center">
                        Iniciar Sesión
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/login-action">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Recordarme</label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Acceder</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
