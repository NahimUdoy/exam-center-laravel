import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/livewire/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                ink: '#0b1f44',
                'ink-soft': '#132a52',
                'ink-mid': '#1a3563',
                fog: '#e8eef8',
                mist: '#f5f8fd',
                paper: '#ffffff',
                brand: {
                    DEFAULT: '#2563eb',
                    deep: '#1d4ed8',
                    soft: '#dbeafe',
                },
                teal: {
                    DEFAULT: '#0d9488',
                    bright: '#14b8a6',
                    glow: '#2dd4bf',
                },
                coral: '#f06a4e',
                amber: '#f59e0b',
                sky: '#38bdf8',
                rose: '#f43f5e',
                muted: '#64748b',
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Sora', ...defaultTheme.fontFamily.sans],
                hand: ['Caveat', 'cursive'],
            },
            boxShadow: {
                premium: '0 20px 50px rgba(37, 99, 235, 0.1)',
                'premium-lg': '0 28px 70px rgba(15, 40, 80, 0.14)',
            },
        },
    },
    plugins: [forms],
};
