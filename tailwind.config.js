import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#edf2ff',
                    100: '#dbe5ff',
                    200: '#bdceff',
                    300: '#95adff',
                    400: '#6582ff',
                    500: '#3f5fff',
                    600: '#2f4fff',
                    700: '#2844e6',
                    800: '#2138bd',
                    900: '#1e3295',
                    950: '#141e52',
                },
                graphite: {
                    50: '#f5f5f6',
                    100: '#e7e8eb',
                    200: '#ced1d7',
                    300: '#a8adb9',
                    400: '#777f8f',
                    500: '#596173',
                    600: '#474e5c',
                    700: '#3a404b',
                    800: '#2d3139',
                    900: '#1e2025',
                    950: '#111216',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
