# 🪑 Proyecto: Tienda de muebles minimalista

Este proyecto es una aplicación web de comercio electrónico desarrollada con **Laravel** para el backend, enfocada en la venta de muebles con un diseño minimalista. La interfaz de usuario utiliza **Bootstrap** para garantizar un diseño responsivo, moderno y limpio, acorde con la estética minimalista de los productos.

-----

## 🚀 Características Principales

* **Autenticación de Usuarios:** Registro e inicio de sesión seguro, con control de intentos fallidos y bloqueo temporal.
* **Panel de Administración:** Vista exclusiva para administradores donde se gestionan usuarios y se visualiza su actividad reciente (fecha de último login).
* **Catálogo de Productos:** Visualización de productos con filtros y categorías (sillas, mesas, lámparas, etc.).
* **Carro de Compras Dinámico:** Funcionalidad para añadir, actualizar y eliminar productos antes de la compra.
* **Gestión de Pedidos:** Sección para que el usuario pueda ver el historial y estado de sus pedidos.
* **Diseño Minimalista:** Uso de Bootstrap y estilos personalizados para una interfaz limpia, enfocada en el producto.

-----
🏗️ Nueva Arquitectura Modular (Orientada a Servicios)
El proyecto ha evolucionado de una estructura monolítica a una arquitectura modular orientada a servicios, separando las responsabilidades en tres aplicaciones independientes construidas con Laravel.

Los 3 Proyectos Principales
api_users (Servicio de Identidad):

Es el núcleo central de seguridad y gestión de usuarios, actuando como Proveedor de Identidad (IdP).

Gestiona el registro, autenticación (Login/Logout) y perfiles.

Utiliza Laravel Sanctum para emitir tokens de acceso (Bearer) basados en permisos (abilities).

Implementa un registro de auditoría (session_logs) para guardar IPs y User-Agents.

api_furniture (Servicio de Recursos):

Gestiona el dominio principal del negocio: catálogo de muebles, categorías y galería de imágenes.

Implementa un middleware personalizado (remote.auth) que intercepta las peticiones y, en lugar de validar el token localmente, consulta a api_users para verificar la identidad y permisos del solicitante.

Incluye su propio sistema de Log de Actividad que registra cada acción administrativa (crear, editar, eliminar).

Principal (Tienda y Consumidor):

Es la interfaz web final orientada al cliente y al administrador.

Actúa como orquestador consumiendo la API de usuarios para autenticación y la API de muebles para mostrar el catálogo.

Maneja la lógica del carrito de compras, el flujo de pagos (integrado con Stripe) y la visualización de pedidos.

🔄 Principales Modificaciones frente a la versión anterior
Bases de datos independientes: La lógica de usuarios y el catálogo de muebles se han movido a APIs y bases de datos independientes para mejorar la escalabilidad y el desacoplamiento.

Validación remota: La API de muebles ahora confía en las validaciones de identidad realizadas remotamente por la API de usuarios mediante comunicación entre servicios.

Control de acceso por "Abilities": Implementación de habilidades de Laravel Sanctum para otorgar permisos granulares según el rol (Admin, Gestor, Cliente).

Pasarela de pago: Integración con Stripe real para garantizar un proceso de compra profesional.

Trazabilidad y Logs: Adición de un apartado de logs detallado para registrar cambios de los administradores y una auditoría completa de sesiones.

Seguridad en Rutas: Eliminación del paso del sesionID a través de las URLs, centralizando la seguridad en el manejo de tokens y sesiones protegidas.

📡 Resumen de Endpoints y Permisos
La comunicación entre la aplicación principal y los servicios se realiza a través de endpoints seguros. Algunos de los principales incluyen:

Autenticación (api_users): /api/login, /api/register, /api/logout, /api/profile.

Gestión de Usuarios (api_users): CRUD completo en /api/users/ protegido mediante las abilities usuarios.ver, usuarios.crear, etc.

Catálogo (api_furniture): /api/furniture (Público para lectura, protegido con la ability muebles.crear/editar/eliminar para modificaciones).

Galería (api_furniture): Gestión avanzada de imágenes en /api/furniture/{id}/gallery, permitiendo subir múltiples fotos y establecer la imagen principal.
## 🛠️ Tecnologías Utilizadas

| Categoría | Tecnología | Versión Aproximada |
| :--- | :--- | :--- |
| **Backend** | **PHP** | 8.x |
| **Framework** | **Laravel** | 10.x / 11.x |
| **Base de Datos** | **MySQL / MariaDB** | - |
| **Frontend** | **Bootstrap** | 5.3 |

-----

## 💻 Instalación y Configuración

Sigue estos pasos para levantar el proyecto en tu entorno local con la base de datos funcionando.

### 1. Clonar el Repositorio

```bash
git clone [URL-DE-TU-REPOSITORIO]
cd [nombre-del-proyecto]
````

### 2\. Configuración del Entorno y Dependencias

1.  **Instalar dependencias de PHP (Composer):**
    ```bash
    composer install
    ```
2.  **Instalar dependencias de Frontend (NPM):**
    ```bash
    npm install
    ```
3.  **Configurar variables de entorno:**
      * Copia el archivo de ejemplo:
        ```bash
        cp .env.example .env
        ```
      * Abre el archivo `.env` y configura tus credenciales de base de datos:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=nombre_de_tu_base_de_datos
        DB_USERNAME=root
        DB_PASSWORD=
        ```
4.  **Generar la clave de la aplicación:**
    ```bash
    php artisan key:generate
    ```

### 3\. Base de Datos y Seeders

Una vez creada la base de datos en tu gestor (phpMyAdmin, Workbench, etc.), ejecuta las migraciones para crear las tablas y los seeders para poblar los datos de prueba:

1.  **Ejecutar migraciones (Crea las tablas):**

    ```bash
    php artisan migrate
    ```

2.  **Ejecutar Seeders (Opcional - Para datos de prueba):**

      * Para poblar toda la base de datos (usuarios, productos, roles):
        ```bash
        php artisan db:seed
        ```
      * *Nota: Esto creará usuarios con roles de Administrador y Cliente para pruebas.*

### 4\. Iniciar el Proyecto

Necesitarás dos terminales abiertas:

**Terminal 1 (Compilación de estilos/JS en tiempo real):**

```bash
npm run dev
```

**Terminal 2 (Servidor de Laravel):**

```bash
php artisan serve
```

El proyecto estará disponible en `http://127.0.0.1:8000`.

-----

## 🔐 Usuarios de Prueba (Seeders)

Si has ejecutado los seeders, puedes usar las siguientes credenciales para acceder:

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | admin@ejemplo.com | password |
| **Cliente** | usuario@ejemplo.com | password |

*(Ajusta estos datos según lo que hayas definido en tus Seeders)*

-----

## 👥 Contribuciones

Las contribuciones son bienvenidas. Si tienes sugerencias o quieres reportar un error, por favor abre un *Issue* o envía un *Pull Request*.

1.  Haz un *fork* del proyecto.
2.  Crea una rama para tu característica (`git checkout -b feature/nueva-funcionalidad`).
3.  Haz *commit* de tus cambios (`git commit -am 'Añadir nueva funcionalidad X'`).
4.  Sube tu rama (`git push origin feature/nueva-funcionalidad`).
5.  Abre un *Pull Request*.

-----

## 📄 Licencia

Este proyecto está bajo la licencia **MIT**. Consulta el archivo `LICENSE` para más detalles.

-----

*Desarrollado por [Azael, Josue, Daniel, Yanira y Jose Antonio]*
