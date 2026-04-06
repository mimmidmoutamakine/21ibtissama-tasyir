import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    safelist: [
        'border-rose-500',
        'bg-rose-500',
        'border-sky-500',
        'bg-sky-500',
        'border-amber-400',
        'bg-amber-400',
        'border-emerald-500',
        'bg-emerald-500',
        'border-slate-300',
        'bg-white',
        'shadow-[0_6px_18px_rgba(244,63,94,0.22)]',
        'shadow-[0_6px_18px_rgba(14,165,233,0.22)]',
        'shadow-[0_6px_18px_rgba(251,191,36,0.20)]',
        'shadow-[0_6px_18px_rgba(16,185,129,0.22)]',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
