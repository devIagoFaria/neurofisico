import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/filament/admin/theme.css',
                // 'resources/assets/css/smart-card-pdf.css',
                'resources/js/front/third-party.js',
                'resources/js/front/front.js',
                'resources/css/front/third-party.css',
                'resources/css/front/scss/main.scss',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
