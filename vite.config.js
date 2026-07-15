import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
            // Limit parallel file ops on RPi3 to avoid OOM
            maxParallelFileOps: 2,
        },
        // Inline small assets to save HTTP round-trips on weak connections
        assetsInlineLimit: 4096,
        sourcemap: false,
        chunkSizeWarningLimit: 800,
    },
    server: {
        host: '127.0.0.1',
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
