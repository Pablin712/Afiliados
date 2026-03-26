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
    <body x-data="{ mobileMenuOpen: false }" class="font-sans bg-gray-100 text-gray-900 dark:bg-graphite-950 dark:text-graphite-100 antialiased">
        <div class="min-h-screen">
            <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur dark:bg-graphite-900/95 dark:border-graphite-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                    <a href="#inicio" class="flex items-center gap-3 shrink-0">
                        <x-application-logo class="h-10 w-auto" src="{{ asset('storage/siglas2.png') }}" />
                        <span class="hidden sm:inline text-sm font-semibold text-gray-800 dark:text-graphite-100">AET Trader Academy</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600 dark:text-graphite-300">
                        <a href="#inicio" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.home') }}</a>
                        <a href="#programas" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.programs') }}</a>
                        <a href="#quien-detras" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.about_behind') }}</a>
                        <a href="#testimonios" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.testimonials') }}</a>
                        <a href="#contacto" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.contact') }}</a>
                    </nav>

                    <div class="flex items-center gap-2">
                        <x-language-switcher />

                        <button
                            type="button"
                            aria-label="{{ __('messages.nav.change_theme') }}"
                            title="{{ __('messages.nav.change_theme') }}"
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

                        <button
                            type="button"
                            class="md:hidden inline-flex items-center justify-center p-2 rounded-md border border-gray-300 text-gray-600 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400"
                            x-on:click="mobileMenuOpen = !mobileMenuOpen"
                            :aria-expanded="mobileMenuOpen.toString()"
                            aria-controls="mobile-main-nav"
                        >
                            <span class="sr-only">{{ __('messages.nav.toggle_menu') }}</span>
                            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <path :class="{ 'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>

                        @if (Route::has('login'))
                            @auth
                                <a href="{{ route('plans.index') }}" class="hidden md:inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                    {{ __('messages.nav.plans') }}
                                </a>
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.nav.dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                    {{ __('messages.nav.login') }}
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>

                <div id="mobile-main-nav" x-cloak x-show="mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white dark:border-graphite-800 dark:bg-graphite-900">
                    <nav class="px-4 py-3 space-y-2 text-sm font-medium text-gray-700 dark:text-graphite-200">
                        <a href="#inicio" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.home') }}</a>
                        <a href="#programas" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.programs') }}</a>
                        <a href="#quien-detras" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.about_behind') }}</a>
                        <a href="#testimonios" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.testimonials') }}</a>
                        <a href="#contacto" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.contact') }}</a>
                    </nav>

                    @if (Route::has('login'))
                        <div class="px-4 pb-4 pt-2 border-t border-gray-200 dark:border-graphite-800 space-y-2">
                            @auth
                                <a href="{{ route('plans.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                    {{ __('messages.nav.plans') }}
                                </a>
                                <a href="{{ route('dashboard') }}" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.nav.dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                    {{ __('messages.nav.login') }}
                                </a>
                            @endauth
                        </div>
                    @endif
                </div>
            </header>

            <main id="inicio" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <section class="rounded-3xl border border-gray-200 bg-white p-8 sm:p-10 lg:p-14 shadow-sm dark:border-graphite-800 dark:bg-graphite-900 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.1fr)_420px] gap-10 items-center">
                        <div class="max-w-3xl">
                            <p class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold tracking-wide text-brand-700 dark:border-brand-900 dark:bg-brand-950/50 dark:text-brand-300">
                                {{ __('messages.welcome.section_badge') }}
                            </p>

                            <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ __('messages.welcome.main_title') }}
                            </h1>

                            <p class="mt-5 text-base sm:text-lg text-gray-600 dark:text-graphite-300 leading-relaxed">
                                {{ __('messages.welcome.main_description') }}
                            </p>

                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="#programas" class="inline-flex items-center px-5 py-3 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.welcome.button_programs') }}
                                </a>
                                <a href="#contacto" class="inline-flex items-center px-5 py-3 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                    {{ __('messages.welcome.button_advisor') }}
                                </a>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="absolute -top-10 -right-8 h-40 w-40 rounded-full bg-brand-100/80 blur-3xl dark:bg-brand-800/30"></div>
                            <div class="absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-sky-100/80 blur-3xl dark:bg-sky-800/20"></div>
                            <div class="relative flex flex-col items-center justify-center px-4 py-6 sm:px-6 sm:py-8">
                                <img src="{{ asset('storage/letras_cuadrado.jpeg') }}" alt="AET Trader Academy logo" class="mx-auto block w-full max-w-[280px] dark:hidden">
                                <img src="{{ asset('storage/logo.jpg') }}" alt="AET Trader Academy logo dark" class="mx-auto hidden w-full max-w-[280px] dark:block">
                                <div class="mt-5 text-center max-w-sm">
                                    <p class="text-sm font-semibold tracking-wide text-gray-900 dark:text-graphite-100">AET Trader Academy</p>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.hero_logo_caption') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="programas" class="mt-10 sm:mt-14">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ __('messages.welcome.programs_title') }}
                            </h2>
                            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-graphite-300">
                                {{ __('messages.welcome.programs_description') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <article class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                            <div class="aspect-[2/3] bg-gray-100 dark:bg-graphite-800">
                                <img src="{{ asset('storage/programs/program1.jpeg') }}" alt="{{ __('messages.welcome.program_card_1_title') }}" class="h-full w-full object-cover object-center">
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.program_card_1_title') }}</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.program_card_1_desc') }}</p>
                            </div>
                        </article>

                        <article class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                            <div class="aspect-[2/3] bg-gray-100 dark:bg-graphite-800">
                                <img src="{{ asset('storage/programs/beneficios1.jpeg') }}" alt="{{ __('messages.welcome.program_card_2_title') }}" class="h-full w-full object-cover object-center">
                            </div>
                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.program_card_2_title') }}</h3>
                                <p class="mt-2 text-sm text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.program_card_2_desc') }}</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section id="quien-detras" class="mt-10 sm:mt-14 rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                        <div class="lg:col-span-2">
                            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ __('messages.welcome.behind_title') }}
                            </h2>
                            <p class="mt-3 text-sm sm:text-base text-gray-600 dark:text-graphite-300 leading-relaxed">
                                {{ __('messages.welcome.behind_description') }}
                            </p>

                            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-gray-200 dark:border-graphite-800 px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-graphite-400">{{ __('messages.welcome.behind_focus_1_label') }}</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-graphite-100">{{ __('messages.welcome.behind_focus_1_value') }}</p>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-graphite-800 px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-graphite-400">{{ __('messages.welcome.behind_focus_2_label') }}</p>
                                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-graphite-100">{{ __('messages.welcome.behind_focus_2_value') }}</p>
                                </div>
                            </div>
                        </div>

                        <aside class="rounded-2xl border border-gray-200 dark:border-graphite-800 bg-gray-50 dark:bg-graphite-800/60 p-5">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/siglas2.png') }}" alt="AET Trader Academy" class="h-12 w-12 rounded-full object-cover">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">AET Trader Academy</p>
                                    <p class="text-xs text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.behind_badge') }}</p>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.behind_quote') }}</p>
                        </aside>
                    </div>
                </section>

                <section id="testimonios" class="mt-10 sm:mt-14">
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                        {{ __('messages.welcome.testimonials_title') }}
                    </h2>
                    <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-graphite-300">
                        {{ __('messages.welcome.testimonials_description') }}
                    </p>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-5">
                        <article class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                            <div class="aspect-[9/16] bg-gray-100 dark:bg-graphite-800">
                                <img src="{{ asset('storage/testimonios/testimonio1.jpeg') }}" alt="{{ __('messages.welcome.testimonial_1_name') }}" class="h-full w-full object-contain object-center">
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.testimonial_1_name') }}</h3>
                                <p class="mt-1 text-xs text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.testimonial_1_text') }}</p>
                            </div>
                        </article>

                        <article class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                            <div class="aspect-[9/16] bg-gray-100 dark:bg-graphite-800">
                                <img src="{{ asset('storage/testimonios/testimonio2.jpeg') }}" alt="{{ __('messages.welcome.testimonial_2_name') }}" class="h-full w-full object-contain object-center">
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.testimonial_2_name') }}</h3>
                                <p class="mt-1 text-xs text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.testimonial_2_text') }}</p>
                            </div>
                        </article>

                        <article class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                            <div class="aspect-[9/16] bg-gray-100 dark:bg-graphite-800">
                                <img src="{{ asset('storage/testimonios/testimonio3.jpeg') }}" alt="{{ __('messages.welcome.testimonial_3_name') }}" class="h-full w-full object-contain object-center">
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.testimonial_3_name') }}</h3>
                                <p class="mt-1 text-xs text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.testimonial_3_text') }}</p>
                            </div>
                        </article>

                        <article class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                            <div class="aspect-[9/16] bg-gray-100 dark:bg-graphite-800">
                                <img src="{{ asset('storage/testimonios/testimonio4.jpeg') }}" alt="{{ __('messages.welcome.testimonial_4_name') }}" class="h-full w-full object-contain object-center">
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.testimonial_4_name') }}</h3>
                                <p class="mt-1 text-xs text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.testimonial_4_text') }}</p>
                            </div>
                        </article>
                    </div>
                </section>

                <section id="contacto" class="mt-10 sm:mt-14 mb-6 rounded-2xl border border-brand-200 bg-gradient-to-br from-brand-50 via-white to-emerald-50 p-6 sm:p-8 dark:border-brand-900 dark:from-brand-950/30 dark:via-graphite-900 dark:to-emerald-950/20">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10 items-center">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ __('messages.welcome.contact_title') }}
                            </h2>
                            <p class="mt-2 text-sm sm:text-base text-gray-700 dark:text-graphite-200">
                                {{ __('messages.welcome.contact_description') }}
                            </p>

                            <div class="mt-6 space-y-3">
                                <a href="https://t.me/AETSAS" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm4.64 7.06-1.65 7.79c-.12.56-.45.69-.92.43l-2.54-1.87-1.22 1.17c-.13.13-.24.24-.5.24l.18-2.6 4.73-4.27c.21-.18-.04-.29-.32-.11l-5.85 3.68-2.52-.79c-.55-.17-.56-.55.11-.81l9.84-3.79c.46-.17.86.11.66 1.03Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Telegram</p>
                                        <p class="text-xs text-gray-600 dark:text-graphite-300">@AETSAS</p>
                                    </div>
                                </a>

                                <a href="https://www.instagram.com/aet.trader.academy?igsh=cWsxN21qM2o5bmg0&utm_source=qr" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm8.5 1.5h-8.5A4.25 4.25 0 0 0 3.5 7.75v8.5a4.25 4.25 0 0 0 4.25 4.25h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5a4.25 4.25 0 0 0-4.25-4.25ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.25-.88a1.13 1.13 0 1 1 0 2.26 1.13 1.13 0 0 1 0-2.26Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Instagram</p>
                                        <p class="text-xs text-gray-600 dark:text-graphite-300">@aet.trader.academy</p>
                                    </div>
                                </a>

                                <a href="mailto:Aetsas01@gmail.com" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M3 5.75A2.75 2.75 0 0 1 5.75 3h12.5A2.75 2.75 0 0 1 21 5.75v12.5A2.75 2.75 0 0 1 18.25 21H5.75A2.75 2.75 0 0 1 3 18.25V5.75Zm2.1-.89 6.38 5.1a.83.83 0 0 0 1.04 0l6.38-5.1a1.25 1.25 0 0 0-.65-.18H5.75c-.23 0-.45.06-.65.18Zm14.4 2.03-5.96 4.77a2.33 2.33 0 0 1-2.9 0L4.7 6.89v11.36c0 .69.56 1.25 1.25 1.25h12.1c.69 0 1.25-.56 1.25-1.25V6.89Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Correo</p>
                                        <p class="text-xs text-gray-600 dark:text-graphite-300">Aetsas01@gmail.com</p>
                                    </div>
                                </a>

                                <a href="https://wa.me/593978855098" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12.04 2.5c-5.23 0-9.47 4.18-9.47 9.35 0 1.66.44 3.28 1.27 4.7L2.5 21.5l5.08-1.31a9.55 9.55 0 0 0 4.46 1.12h.01c5.23 0 9.47-4.18 9.47-9.35 0-2.49-.99-4.83-2.77-6.59A9.52 9.52 0 0 0 12.04 2.5Zm0 17.2h-.01a7.92 7.92 0 0 1-4.03-1.1l-.29-.17-3.02.78.81-2.93-.19-.3a7.8 7.8 0 0 1-1.23-4.12c0-4.31 3.55-7.82 7.95-7.82 2.12 0 4.11.82 5.61 2.31a7.72 7.72 0 0 1 2.34 5.52c0 4.31-3.56 7.82-7.94 7.82Zm4.35-5.89c-.24-.12-1.4-.69-1.62-.77-.22-.08-.38-.12-.54.12-.16.23-.62.77-.76.92-.14.16-.28.18-.52.06-.24-.12-1-.37-1.9-1.19-.7-.62-1.17-1.39-1.31-1.62-.14-.23-.01-.35.1-.47.1-.1.24-.27.36-.4.12-.14.16-.23.24-.39.08-.16.04-.29-.02-.41-.06-.12-.54-1.29-.74-1.76-.2-.47-.4-.39-.54-.39h-.47c-.16 0-.41.06-.62.29-.22.23-.84.81-.84 1.99s.86 2.3.98 2.46c.12.16 1.68 2.66 4.14 3.63.58.23 1.04.37 1.39.47.58.16 1.1.14 1.52.08.46-.07 1.4-.57 1.6-1.12.2-.55.2-1.02.14-1.12-.06-.1-.22-.16-.46-.27Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">WhatsApp</p>
                                        <p class="text-xs text-gray-600 dark:text-graphite-300">+593 97 885 5098</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-graphite-700 dark:bg-graphite-900">
                            <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.contact_qr_title') }}</p>
                            <p class="mt-1 text-xs text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.contact_qr_description') }}</p>

                            <div class="mt-4 rounded-xl overflow-hidden border border-gray-200 dark:border-graphite-700 bg-gray-50 dark:bg-graphite-800">
                                <img src="{{ asset('storage/contact/telegram.jpeg') }}" alt="{{ __('messages.welcome.contact_qr_alt') }}" class="w-full h-auto object-contain">
                            </div>

                            <a href="https://t.me/AETSAS" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center justify-center w-full px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                {{ __('messages.welcome.contact_qr_button') }}
                            </a>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
