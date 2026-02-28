<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AET Trader Academy') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

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
    <body class="font-sans bg-gray-100 text-gray-900 dark:bg-graphite-950 dark:text-graphite-100 antialiased">
        <div class="min-h-screen">
            <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur dark:bg-graphite-900/95 dark:border-graphite-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                    <a href="#inicio" class="flex items-center gap-3 shrink-0">
                        <x-application-logo class="h-10 w-auto" src="{{ asset('storage/siglas2.png') }}" />
                        <span class="hidden sm:inline text-sm font-semibold text-gray-800 dark:text-graphite-100">AET Trader Academy</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600 dark:text-graphite-300">
                        <a href="#inicio" class="hover:text-brand-600 dark:hover:text-brand-400">Inicio</a>
                        <a href="#programas" class="hover:text-brand-600 dark:hover:text-brand-400">Programas</a>
                        <a href="#mentores" class="hover:text-brand-600 dark:hover:text-brand-400">Mentores</a>
                        <a href="#testimonios" class="hover:text-brand-600 dark:hover:text-brand-400">Testimonios</a>
                        <a href="#contacto" class="hover:text-brand-600 dark:hover:text-brand-400">Contacto</a>
                    </nav>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:text-brand-400"
                            onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                        >
                            Tema
                        </button>

                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                    Entrar
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </header>

            <main id="inicio" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <section class="rounded-2xl border border-gray-200 bg-white p-8 sm:p-10 lg:p-14 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <div class="max-w-3xl">
                        <p class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold tracking-wide text-brand-700 dark:border-brand-900 dark:bg-brand-950/50 dark:text-brand-300">
                            Formación en Trading • Nivel inicial a avanzado
                        </p>

                        <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-graphite-100">
                            Aprende trading con estructura, gestión de riesgo y acompañamiento real.
                        </h1>

                        <p class="mt-5 text-base sm:text-lg text-gray-600 dark:text-graphite-300 leading-relaxed">
                            En AET Trader Academy te guiamos paso a paso para construir una metodología sólida: análisis técnico,
                            psicología del trader y ejecución disciplinada. Esta es la primera sección del sitio; luego iremos agregando
                            más bloques para programas, mentores, testimonios y contacto.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="#programas" class="inline-flex items-center px-5 py-3 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                Ver programas
                            </a>
                            <a href="#contacto" class="inline-flex items-center px-5 py-3 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                Hablar con un asesor
                            </a>
                        </div>
                    </div>
                </section>

                <div id="programas" class="h-16"></div>
                <div id="mentores" class="h-16"></div>
                <div id="testimonios" class="h-16"></div>
                <div id="contacto" class="h-16"></div>
            </main>
        </div>
    </body>
</html>
