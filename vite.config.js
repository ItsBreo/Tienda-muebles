// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // ⬅️ Archivo de Paleta/Global
                'resources/css/paletas.css',
                // ⬅️ Archivo Específico de Página
                'resources/css/adminPanel.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
