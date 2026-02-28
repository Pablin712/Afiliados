<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AET Trader Academy') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('storage/siglas2.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/siglas2.png') }}">

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
                            aria-label="Cambiar tema"
                            title="Cambiar tema"
                            class="inline-flex items-center px-3 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:text-brand-400"
                            onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 dark:hidden" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2Zm0 11.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm7.25-4.25a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1 0-1.5h1.5ZM4.25 10a.75.75 0 0 1-.75.75H2a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 1 .75.75Zm10.667 4.606a.75.75 0 0 1 1.06 1.061l-1.06 1.06a.75.75 0 1 1-1.061-1.06l1.06-1.061Zm-9.834-9.834a.75.75 0 0 1 1.061 0l1.06 1.06a.75.75 0 1 1-1.06 1.061l-1.061-1.06a.75.75 0 0 1 0-1.061Zm11.894 1.061a.75.75 0 0 1-1.06 1.06l-1.061-1.06a.75.75 0 1 1 1.06-1.061l1.061 1.06Zm-9.834 9.834a.75.75 0 1 1-1.06 1.06l-1.061-1.06a.75.75 0 1 1 1.06-1.061l1.061 1.061ZM10 15.75a.75.75 0 0 1 .75.75V18a.75.75 0 0 1-1.5 0v-1.5a.75.75 0 0 1 .75-.75Z" />
                            </svg>
                            <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5 dark:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M11.5 2.25a.75.75 0 0 1 .74.86 6.75 6.75 0 0 0 8.65 7.55.75.75 0 0 1 .89.89A8.25 8.25 0 1 1 10.66.51a.75.75 0 0 1 .84 1.74Z" />
                            </svg>
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
