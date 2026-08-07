import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        rollupOptions: {
            output: {
                // Split the big third-party libraries into their own chunks.
                // They change only when the dependency is upgraded, so with
                // the immutable cache headers in public/.htaccess a returning
                // visitor re-downloads app code alone after a deploy instead
                // of React and friends all over again. Chart/Leaflet/shp stay
                // lazily loaded — only pages importing them fetch them.
                manualChunks(id) {
                    if (!id.includes('node_modules')) return;
                    if (/[\\/]node_modules[\\/](react|react-dom|scheduler)[\\/]/.test(id)) return 'vendor-react';
                    if (/[\\/]node_modules[\\/](chart\.js|react-chartjs-2)[\\/]/.test(id)) return 'vendor-charts';
                    if (/[\\/]node_modules[\\/]leaflet[\\/]/.test(id)) return 'vendor-leaflet';
                    if (/[\\/]node_modules[\\/](shpjs|proj4)[\\/]/.test(id)) return 'vendor-shp';
                },
            },
        },
    },
});
