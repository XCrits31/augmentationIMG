import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'xcrits31.su',
            protocol: 'wss',
            port: 5173,
        },
        watch: {
            ignored: ['**/node_modules/**', '**/vendor/**', '**/venv/**']
        }
    }
});
