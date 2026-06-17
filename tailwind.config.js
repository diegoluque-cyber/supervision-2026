import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                alo: {
                    green: '#00983F',
                    'green-dark': '#007A32',
                    red: '#E40721',
                    'red-dark': '#B8061A',
                    black: '#1B1918',
                    gray: '#F4F4F4',
                    'gray-dark': '#6B7280',
                },
            },
            boxShadow: {
                alo: '0 4px 24px rgba(0, 152, 63, 0.12)',
                card: '0 2px 16px rgba(27, 25, 24, 0.08)',
            },
        },
    },

    plugins: [forms],
};
