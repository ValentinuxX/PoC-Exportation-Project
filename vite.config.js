import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'; // 1. Importo el plugin de Vue

export default defineConfig({
    // configuración del servidor indicando que el host es el de Codespaces
  server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true, // Trato de evitar que cambie de puerto sin avisar
        cors: true,
        headers: {
            'Access-Control-Allow-Origin': '*' // Inyecto la cabecera a la fuerza
        },
        hmr: {
            host: 'cuddly-disco-7755qwx96w6275q-5173.app.github.dev',
            protocol: 'wss',
            clientPort: 443
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({ // 2. Le decimos a Vite que use el plugin
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});

