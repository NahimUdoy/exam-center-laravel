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
                ink: 'rgb(var(--ink) / <alpha-value>)',
                'ink-soft': 'rgb(var(--ink-soft) / <alpha-value>)',
                'ink-mid': 'rgb(var(--ink-mid) / <alpha-value>)',
                fog: 'rgb(var(--fog) / <alpha-value>)',
                mist: 'rgb(var(--mist) / <alpha-value>)',
                paper: 'rgb(var(--paper) / <alpha-value>)',
                brand: {
                    DEFAULT: 'rgb(var(--brand) / <alpha-value>)',
                    deep: 'rgb(var(--brand-deep) / <alpha-value>)',
                    soft: 'rgb(var(--brand-soft) / <alpha-value>)',
                },
                teal: {
                    DEFAULT: 'rgb(var(--teal) / <alpha-value>)',
                    bright: 'rgb(var(--teal-bright) / <alpha-value>)',
                    glow: 'rgb(var(--teal-glow) / <alpha-value>)',
                },
                coral: 'rgb(var(--coral) / <alpha-value>)',
                amber: 'rgb(var(--amber) / <alpha-value>)',
                sky: 'rgb(var(--sky) / <alpha-value>)',
                rose: 'rgb(var(--rose) / <alpha-value>)',
                muted: 'rgb(var(--muted) / <alpha-value>)',
            },
            fontFamily: {
                sans: ['var(--font-sans)', ...defaultTheme.fontFamily.sans],
                display: ['var(--font-display)', ...defaultTheme.fontFamily.serif],
                hand: ['Caveat', 'cursive'],
            },
            boxShadow: {
                premium: 'var(--shadow)',
                'premium-lg': 'var(--shadow-lg)',
                deck: 'var(--shadow-deck)',
            },
        },
    },
    plugins: [forms],
};
