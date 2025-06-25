import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            refresh: [
                ...refreshPaths, // Keep Laravel's default watch paths
                'app/Http/Livewire/**', // If you have custom Livewire components outside Filament
                'app/Filament/**',     // Key for Filament hot reload
            ],
        }),
        tailwindcss(),

    ],
});
