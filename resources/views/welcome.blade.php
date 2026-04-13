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
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.register') }}
                                </a>
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
                                <a href="{{ route('register') }}" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.register') }}
                                </a>
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
                                <a href="https://deriv.com/signup?sidc=7044F2C1-1A0C-496A-986E-570DCAD80FF8&utm_campaign=dynamicworks&utm_medium=affiliate&utm_source=CU17859" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-5 py-3 rounded-md bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 dark:bg-brand-500 dark:hover:bg-brand-400">
                                    {{ __('messages.welcome.button_deriv_account') }}
                                </a>
                                <a href="https://es.gowt.net/ib61404" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-5 py-3 rounded-md border border-sky-300 bg-sky-50 text-sm font-semibold text-sky-800 hover:border-sky-400 hover:bg-sky-100 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:border-sky-400 dark:hover:bg-sky-500/20">
                                    {{ __('messages.welcome.button_weltrade_account') }}
                                </a>
                            </div>

                            <p class="mt-4 text-sm text-gray-500 dark:text-graphite-400">
                                {{ __('messages.welcome.trading_accounts_hint') }}
                            </p>
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
                    <div class="programs-showcase relative overflow-hidden rounded-[2rem] border border-slate-200/70 p-6 sm:p-8 lg:p-10 shadow-[0_28px_70px_-34px_rgba(15,23,42,0.75)] dark:border-brand-900/40">
                        <div class="absolute inset-0 opacity-70">
                            <div class="programs-orb program-float absolute -left-16 top-10 h-36 w-36 rounded-full bg-brand-500/20 blur-3xl"></div>
                            <div class="programs-orb program-float-delayed absolute right-0 top-0 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl"></div>
                            <div class="programs-orb program-pulse-soft absolute bottom-0 left-1/3 h-44 w-44 rounded-full bg-emerald-400/10 blur-3xl"></div>
                        </div>

                        <div class="relative grid grid-cols-1 gap-8 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] xl:gap-10">
                            <div>
                                <span class="program-fade-up inline-flex items-center rounded-full border border-white/15 bg-white/8 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-brand-100 backdrop-blur" style="animation-delay: 80ms;">
                                    {{ __('messages.welcome.programs_badge') }}
                                </span>

                                <h2 class="program-fade-up mt-5 max-w-3xl text-3xl font-extrabold tracking-tight text-white sm:text-4xl" style="animation-delay: 160ms;">
                                    {{ __('messages.welcome.programs_title') }}
                                </h2>

                                <p class="program-fade-up mt-4 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base" style="animation-delay: 240ms;">
                                    {{ __('messages.welcome.programs_description') }}
                                </p>

                                <div class="program-fade-up mt-6 flex flex-wrap gap-3" style="animation-delay: 320ms;">
                                    <span class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ __('messages.welcome.programs_pill_1') }}</span>
                                    <span class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ __('messages.welcome.programs_pill_2') }}</span>
                                    <span class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ __('messages.welcome.programs_pill_3') }}</span>
                                    <span class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ __('messages.welcome.programs_pill_4') }}</span>
                                </div>

                                <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
                                    <article class="program-fade-up rounded-[1.6rem] border border-white/12 bg-white/8 p-5 shadow-[0_20px_50px_-34px_rgba(15,23,42,0.95)] backdrop-blur-md" style="animation-delay: 400ms;">
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-brand-200">{{ __('messages.welcome.program_card_1_eyebrow') }}</p>
                                        <h3 class="mt-3 text-xl font-semibold text-white">{{ __('messages.welcome.program_card_1_title') }}</h3>
                                        <p class="mt-3 text-sm leading-6 text-slate-300">{{ __('messages.welcome.program_card_1_desc') }}</p>
                                        <div class="mt-6 grid gap-3">
                                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm font-medium text-white">{{ __('messages.welcome.program_card_1_item_1_title') }}</div>
                                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm font-medium text-white">{{ __('messages.welcome.program_card_1_item_2_title') }}</div>
                                            <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm font-medium text-white">{{ __('messages.welcome.program_card_1_item_3_title') }}</div>
                                        </div>
                                    </article>

                                    <article class="program-fade-up rounded-[1.6rem] border border-white/12 bg-slate-950/40 p-5 shadow-[0_20px_50px_-34px_rgba(15,23,42,0.95)] backdrop-blur-md" style="animation-delay: 480ms;">
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-200">{{ __('messages.welcome.program_card_2_eyebrow') }}</p>
                                        <h3 class="mt-3 text-xl font-semibold text-white">{{ __('messages.welcome.program_card_2_title') }}</h3>
                                        <p class="mt-3 text-sm leading-6 text-slate-300">{{ __('messages.welcome.program_card_2_desc') }}</p>

                                        <div class="mt-6 space-y-3">
                                            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                                                <p class="text-sm font-semibold text-white">{{ __('messages.welcome.program_card_2_item_1_title') }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                                                <p class="text-sm font-semibold text-white">{{ __('messages.welcome.program_card_2_item_2_title') }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                                                <p class="text-sm font-semibold text-white">{{ __('messages.welcome.program_card_2_item_3_title') }}</p>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            </div>

                            <aside class="program-fade-up relative rounded-[1.8rem] border border-white/12 bg-white/8 p-6 shadow-[0_20px_60px_-36px_rgba(15,23,42,0.95)] backdrop-blur-md" style="animation-delay: 560ms;">
                                <div class="absolute -right-10 top-8 h-24 w-24 rounded-full border border-white/10"></div>
                                <div class="absolute -right-6 top-12 h-16 w-16 rounded-full border border-brand-300/20"></div>

                                <div class="flex items-center gap-4">
                                    <div class="program-logo-shell program-pulse-soft flex h-20 w-20 items-center justify-center rounded-[1.6rem] border border-brand-300/20 bg-brand-500/12 shadow-[0_12px_30px_-18px_rgba(63,95,255,0.95)]">
                                        <img src="{{ asset('storage/siglas2.png') }}" alt="AET Trader Academy" class="h-12 w-12 object-contain">
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">{{ __('messages.welcome.programs_panel_badge') }}</p>
                                        <p class="mt-2 text-2xl font-semibold tracking-tight text-white">AET Trader Academy</p>
                                    </div>
                                </div>

                                <p class="mt-6 text-lg font-semibold leading-8 text-white">
                                    {{ __('messages.welcome.programs_panel_title') }}
                                </p>
                                <p class="mt-3 text-sm leading-7 text-slate-300">
                                    {{ __('messages.welcome.programs_panel_description') }}
                                </p>

                                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3 xl:grid-cols-1">
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ __('messages.welcome.programs_stat_1_label') }}</p>
                                        <p class="mt-2 text-sm font-semibold text-white">{{ __('messages.welcome.programs_stat_1_value') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ __('messages.welcome.programs_stat_2_label') }}</p>
                                        <p class="mt-2 text-sm font-semibold text-white">{{ __('messages.welcome.programs_stat_2_value') }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ __('messages.welcome.programs_stat_3_label') }}</p>
                                        <p class="mt-2 text-sm font-semibold text-white">{{ __('messages.welcome.programs_stat_3_value') }}</p>
                                    </div>
                                </div>

                                <a href="#contacto" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:ring-offset-2 focus:ring-offset-slate-950">
                                    {{ __('messages.welcome.programs_cta') }}
                                </a>
                            </aside>
                        </div>
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

                <section id="contacto" x-data="{ contactTab: 'new' }" class="mt-10 sm:mt-14 mb-6 rounded-[2rem] border border-brand-200 bg-gradient-to-br from-brand-50 via-white to-emerald-50 p-6 sm:p-8 dark:border-brand-900 dark:from-brand-950/30 dark:via-graphite-900 dark:to-emerald-950/20">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.1fr)_420px] lg:gap-10 items-start">
                        <div>
                            <span class="inline-flex rounded-full border border-brand-200 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 shadow-sm dark:border-brand-900 dark:bg-brand-950/30 dark:text-brand-300">
                                {{ __('messages.welcome.contact_badge') }}
                            </span>

                            <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ __('messages.welcome.contact_title') }}
                            </h2>
                            <p class="mt-2 max-w-2xl text-sm sm:text-base text-gray-700 dark:text-graphite-200 leading-7">
                                {{ __('messages.welcome.contact_description') }}
                            </p>

                            <div class="mt-6 inline-flex rounded-2xl border border-gray-200 bg-white/80 p-1 shadow-sm dark:border-graphite-700 dark:bg-graphite-900/80">
                                <button
                                    type="button"
                                    x-on:click="contactTab = 'new'"
                                    :class="contactTab === 'new' ? 'bg-slate-900 text-white shadow-sm dark:bg-brand-500' : 'text-gray-600 hover:text-gray-900 dark:text-graphite-300 dark:hover:text-white'"
                                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                                >
                                    {{ __('messages.welcome.contact_tab_new') }}
                                </button>
                                <button
                                    type="button"
                                    x-on:click="contactTab = 'trader'"
                                    :class="contactTab === 'trader' ? 'bg-slate-900 text-white shadow-sm dark:bg-brand-500' : 'text-gray-600 hover:text-gray-900 dark:text-graphite-300 dark:hover:text-white'"
                                    class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                                >
                                    {{ __('messages.welcome.contact_tab_trader') }}
                                </button>
                            </div>

                            <div class="mt-6">
                                <div x-show="contactTab === 'new'" x-transition.opacity.duration.250ms class="space-y-5">
                                    <div class="rounded-[1.6rem] border border-white/60 bg-white/85 p-5 shadow-[0_18px_50px_-34px_rgba(15,23,42,0.75)] backdrop-blur dark:border-graphite-700 dark:bg-graphite-900/85">
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-700 dark:text-brand-300">{{ __('messages.welcome.contact_new_eyebrow') }}</p>
                                        <h3 class="mt-3 text-xl font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.contact_new_title') }}</h3>
                                        <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.contact_new_description') }}</p>

                                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-graphite-800 dark:bg-graphite-950/60">
                                                <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">01</p>
                                                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.contact_new_step_1') }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-graphite-800 dark:bg-graphite-950/60">
                                                <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">02</p>
                                                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.contact_new_step_2') }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-graphite-800 dark:bg-graphite-950/60">
                                                <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">03</p>
                                                <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.contact_new_step_3') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <a href="https://t.me/AETSAS" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm4.64 7.06-1.65 7.79c-.12.56-.45.69-.92.43l-2.54-1.87-1.22 1.17c-.13.13-.24.24-.5.24l.18-2.6 4.73-4.27c.21-.18-.04-.29-.32-.11l-5.85 3.68-2.52-.79c-.55-.17-.56-.55.11-.81l9.84-3.79c.46-.17.86.11.66 1.03Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Telegram</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">@AETSAS</p>
                                            </div>
                                        </a>

                                        <a href="https://www.instagram.com/aet.trader.academy?igsh=cWsxN21qM2o5bmg0&utm_source=qr" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm8.5 1.5h-8.5A4.25 4.25 0 0 0 3.5 7.75v8.5a4.25 4.25 0 0 0 4.25 4.25h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5a4.25 4.25 0 0 0-4.25-4.25ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.25-.88a1.13 1.13 0 1 1 0 2.26 1.13 1.13 0 0 1 0-2.26Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Instagram</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">@aet.trader.academy</p>
                                            </div>
                                        </a>

                                        <a href="mailto:Aetsas01@gmail.com" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M3 5.75A2.75 2.75 0 0 1 5.75 3h12.5A2.75 2.75 0 0 1 21 5.75v12.5A2.75 2.75 0 0 1 18.25 21H5.75A2.75 2.75 0 0 1 3 18.25V5.75Zm2.1-.89 6.38 5.1a.83.83 0 0 0 1.04 0l6.38-5.1a1.25 1.25 0 0 0-.65-.18H5.75c-.23 0-.45.06-.65.18Zm14.4 2.03-5.96 4.77a2.33 2.33 0 0 1-2.9 0L4.7 6.89v11.36c0 .69.56 1.25 1.25 1.25h12.1c.69 0 1.25-.56 1.25-1.25V6.89Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.email') }}</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">Aetsas01@gmail.com</p>
                                            </div>
                                        </a>

                                        <a href="https://wa.me/593978855098" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
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

                                <div x-show="contactTab === 'trader'" x-transition.opacity.duration.250ms class="space-y-5" style="display: none;">
                                    <div class="rounded-[1.6rem] border border-slate-200 bg-slate-950 p-5 shadow-[0_18px_50px_-34px_rgba(15,23,42,0.95)] dark:border-brand-900/50">
                                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-200">{{ __('messages.welcome.contact_trader_eyebrow') }}</p>
                                        <h3 class="mt-3 text-xl font-semibold text-white">{{ __('messages.welcome.contact_trader_title') }}</h3>
                                        <p class="mt-3 text-sm leading-7 text-slate-300">{{ __('messages.welcome.contact_trader_description') }}</p>

                                        <div class="mt-5 space-y-3">
                                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                                <p class="text-sm font-semibold text-white">{{ __('messages.welcome.contact_trader_item_1_title') }}</p>
                                                <p class="mt-1 text-sm leading-6 text-slate-300">{{ __('messages.welcome.contact_trader_item_1_desc') }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                                <p class="text-sm font-semibold text-white">{{ __('messages.welcome.contact_trader_item_2_title') }}</p>
                                                <p class="mt-1 text-sm leading-6 text-slate-300">{{ __('messages.welcome.contact_trader_item_2_desc') }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                                                <p class="text-sm font-semibold text-white">{{ __('messages.welcome.contact_trader_item_3_title') }}</p>
                                                <p class="mt-1 text-sm leading-6 text-slate-300">{{ __('messages.welcome.contact_trader_item_3_desc') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <a href="https://wa.me/593978855098?text=Hola%20soy%20trader%20quiero%20trabajar%20con%20ustedes." target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:hover:border-emerald-400 dark:hover:bg-emerald-500/20 sm:col-span-2">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12.04 2.5c-5.23 0-9.47 4.18-9.47 9.35 0 1.66.44 3.28 1.27 4.7L2.5 21.5l5.08-1.31a9.55 9.55 0 0 0 4.46 1.12h.01c5.23 0 9.47-4.18 9.47-9.35 0-2.49-.99-4.83-2.77-6.59A9.52 9.52 0 0 0 12.04 2.5Zm0 17.2h-.01a7.92 7.92 0 0 1-4.03-1.1l-.29-.17-3.02.78.81-2.93-.19-.3a7.8 7.8 0 0 1-1.23-4.12c0-4.31 3.55-7.82 7.95-7.82 2.12 0 4.11.82 5.61 2.31a7.72 7.72 0 0 1 2.34 5.52c0 4.31-3.56 7.82-7.94 7.82Zm4.35-5.89c-.24-.12-1.4-.69-1.62-.77-.22-.08-.38-.12-.54.12-.16.23-.62.77-.76.92-.14.16-.28.18-.52.06-.24-.12-1-.37-1.9-1.19-.7-.62-1.17-1.39-1.31-1.62-.14-.23-.01-.35.1-.47.1-.1.24-.27.36-.4.12-.14.16-.23.24-.39.08-.16.04-.29-.02-.41-.06-.12-.54-1.29-.74-1.76-.2-.47-.4-.39-.54-.39h-.47c-.16 0-.41.06-.62.29-.22.23-.84.81-.84 1.99s.86 2.3.98 2.46c.12.16 1.68 2.66 4.14 3.63.58.23 1.04.37 1.39.47.58.16 1.1.14 1.52.08.46-.07 1.4-.57 1.6-1.12.2-.55.2-1.02.14-1.12-.06-.1-.22-.16-.46-.27Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">{{ __('messages.welcome.contact_trader_whatsapp_title') }}</p>
                                                <p class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('messages.welcome.contact_trader_whatsapp_desc') }}</p>
                                            </div>
                                        </a>

                                        <a href="mailto:Aetsas01@gmail.com?subject=Trader%20interesado%20en%20trabajar%20con%20AET" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M3 5.75A2.75 2.75 0 0 1 5.75 3h12.5A2.75 2.75 0 0 1 21 5.75v12.5A2.75 2.75 0 0 1 18.25 21H5.75A2.75 2.75 0 0 1 3 18.25V5.75Zm2.1-.89 6.38 5.1a.83.83 0 0 0 1.04 0l6.38-5.1a1.25 1.25 0 0 0-.65-.18H5.75c-.23 0-.45.06-.65.18Zm14.4 2.03-5.96 4.77a2.33 2.33 0 0 1-2.9 0L4.7 6.89v11.36c0 .69.56 1.25 1.25 1.25h12.1c.69 0 1.25-.56 1.25-1.25V6.89Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.contact_trader_email_title') }}</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">Aetsas01@gmail.com</p>
                                            </div>
                                        </a>

                                        <a href="https://t.me/AETSAS" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm4.64 7.06-1.65 7.79c-.12.56-.45.69-.92.43l-2.54-1.87-1.22 1.17c-.13.13-.24.24-.5.24l.18-2.6 4.73-4.27c.21-.18-.04-.29-.32-.11l-5.85 3.68-2.52-.79c-.55-.17-.56-.55.11-.81l9.84-3.79c.46-.17.86.11.66 1.03Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Telegram</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">@AETSAS</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative overflow-hidden rounded-[1.8rem] border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-graphite-700 dark:bg-graphite-900">
                            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-brand-100/70 blur-3xl dark:bg-brand-700/20"></div>

                            <div x-show="contactTab === 'new'" x-transition.opacity.duration.250ms class="relative">
                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.welcome.contact_qr_title') }}</p>
                                <p class="mt-1 text-xs leading-6 text-gray-600 dark:text-graphite-300">{{ __('messages.welcome.contact_qr_description') }}</p>

                                <div class="mt-4 rounded-xl overflow-hidden border border-gray-200 dark:border-graphite-700 bg-gray-50 dark:bg-graphite-800">
                                    <img src="{{ asset('storage/contact/telegram.jpeg') }}" alt="{{ __('messages.welcome.contact_qr_alt') }}" class="w-full h-auto object-contain">
                                </div>

                                <a href="https://t.me/AETSAS" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center justify-center w-full px-4 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.welcome.contact_qr_button') }}
                                </a>
                            </div>

                            <div x-show="contactTab === 'trader'" x-transition.opacity.duration.250ms class="relative space-y-4" style="display: none;">
                                <div class="rounded-[1.4rem] border border-slate-200 bg-slate-950 p-5 dark:border-brand-900/50">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-200">{{ __('messages.welcome.contact_trader_side_badge') }}</p>
                                    <h3 class="mt-3 text-xl font-semibold text-white">{{ __('messages.welcome.contact_trader_side_title') }}</h3>
                                    <p class="mt-3 text-sm leading-7 text-slate-300">{{ __('messages.welcome.contact_trader_side_description') }}</p>
                                </div>

                                <div class="rounded-[1.4rem] border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                                    <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">{{ __('messages.welcome.contact_trader_ready_message_label') }}</p>
                                    <p class="mt-2 rounded-xl bg-white/80 px-4 py-3 text-sm text-emerald-800 shadow-sm dark:bg-graphite-950/70 dark:text-emerald-200">
                                        {{ __('messages.welcome.contact_trader_ready_message') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
