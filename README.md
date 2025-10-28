# 🪑 Proyecto: Tienda de muebles minimalista

Este proyecto es una aplicación web de comercio electrónico desarrollada con **Laravel** para el backend, enfocada en la venta de muebles con un diseño minimalista. La interfaz de usuario utiliza **Bootstrap** para garantizar un diseño responsivo, moderno y limpio, acorde con la estética minimalista de los productos.

-----

## 🚀 Características Principales

  * **Autenticación de Usuarios:** Registro e inicio de sesión seguro para clientes.
  * **Catálogo de Productos:** Visualización de productos con filtros y categorías (sillas, mesas, lámparas, etc.).
  * **Carro de Compras Dinámico:** Funcionalidad para añadir, actualizar y eliminar productos antes de la compra.
  * **Gestión de Pedidos:** Sección para que el usuario pueda ver el historial y estado de sus pedidos.
  * **Diseño Minimalista:** Uso de Bootstrap y estilos personalizados para una interfaz limpia, enfocada en el producto.

-----

## 🛠️ Tecnologías Utilizadas

| Categoría | Tecnología | Versión Aproximada |
| :--- | :--- | :--- |
| **Backend** | **PHP** | 8.x |
| **Framework** | **Laravel** | 12.x |
| **Frontend** | **Bootstrap** | 5.0 |

-----

## 💻 Instalación y Configuración

Sigue estos pasos para levantar el proyecto en tu entorno local.

### 1\. Clonar el Repositorio

```bash
git clone [URL-DE-TU-REPOSITORIO]
cd [nombre-del-proyecto]
```

### 2\. Configuración del Entorno

1.  **Instalar dependencias de Composer:**
    ```bash
    composer install
    ```
2.  **Copiar el archivo de entorno:**
    ```bash
    cp .env.example .env
    ```
3.  **Generar la clave de la aplicación:**
    ```bash
    php artisan key:generate
    ```
4.  **Configurar la Base de Datos:** Abre el archivo `.env` y configura las credenciales de tu base de datos (DB\_DATABASE, DB\_USERNAME, DB\_PASSWORD).

### 3\. Ejecutar Migraciones y Seeds

Ejecuta las migraciones para crear las tablas de la base de datos y los **seeds** para poblar las tablas con datos de prueba (productos, categorías, etc.).

```bash
php artisan migrate --seed
```

### 4\. Compilación de Assets (CSS/JS)

Este proyecto utiliza **Vite** para compilar los archivos de Bootstrap y JavaScript.

1.  **Instalar dependencias de Node:**
    ```bash
    npm install
    ```
2.  **Compilar y monitorear los cambios (modo desarrollo):**
    ```bash
    npm run dev
    ```
    O compilar para producción:
    ```bash
    npm run build
    ```

### 5\. Iniciar el Servidor de Laravel

Abre una nueva terminal (mientras `npm run dev` se ejecuta en la otra) e inicia el servidor de desarrollo de Laravel:

```bash
php artisan serve
```

El proyecto estará disponible en `http://127.0.0.1:8000`.

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
