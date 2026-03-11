<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('storage/siglas2.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/siglas2.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            (() => {
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const useDark = savedTheme ? savedTheme === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', useDark);
            })();
        </script>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100 dark:text-graphite-100 dark:bg-graphite-950">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-graphite-950">
            <div>
                <a href="/">
                    <x-application-logo class="w-24 h-24" src="{{ asset('storage/siglas2.png') }}" />
                </a>
            </div>

            <div class="mt-4">
                <x-language-switcher />
            </div>

            <div @class([
                'w-full mt-6 px-6 py-4 bg-white border border-gray-200 dark:bg-graphite-900 dark:border-graphite-800 shadow-md overflow-hidden sm:rounded-lg',
                'sm:max-w-4xl' => request()->routeIs('register'),
                'sm:max-w-md' => ! request()->routeIs('register'),
            ])>
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
