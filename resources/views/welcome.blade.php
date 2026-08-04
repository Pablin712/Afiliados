<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'AET SAS') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('storage/aet-logo-light.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/aet-logo-light.png') }}">

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
        @if ($isPreview ?? false)
            <style>
                .lc-preview-highlight {
                    outline: 3px solid #f59e0b !important;
                    outline-offset: 3px;
                    border-radius: 6px;
                    scroll-margin-top: 100px;
                    animation: lc-preview-pulse 1.4s ease-in-out infinite;
                }
                @keyframes lc-preview-pulse {
                    0%, 100% { outline-color: #f59e0b; }
                    50% { outline-color: #fbbf24; }
                }
            </style>
        @endif
    </head>
    <body x-data="{ mobileMenuOpen: false }" class="font-sans bg-gray-100 text-gray-900 dark:bg-graphite-950 dark:text-graphite-100 antialiased">
        <div class="min-h-screen">
            <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur dark:bg-graphite-900/95 dark:border-graphite-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                    <a href="#inicio" class="flex items-center gap-3 shrink-0">
                        <x-application-logo class="h-10 w-auto" />
                        <span class="hidden sm:inline text-sm font-semibold text-gray-800 dark:text-graphite-100">AET SAS</span>
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
                            <p data-lc-key="section_badge" class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold tracking-wide text-brand-700 dark:border-brand-900 dark:bg-brand-950/50 dark:text-brand-300">
                                {{ $content['section_badge'] }}
                            </p>

                            <h1 data-lc-key="main_title" class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ $content['main_title'] }}
                            </h1>

                            <p data-lc-key="main_description" class="mt-5 text-base sm:text-lg text-gray-600 dark:text-graphite-300 leading-relaxed">
                                {{ $content['main_description'] }}
                            </p>

                            <div class="mt-8 flex flex-wrap gap-3">
                                @foreach ($heroButtons as $button)
                                    <a
                                        data-lc-hero-button="{{ $button->id }}"
                                        href="{{ $button->url }}"
                                        @if ($button->opensExternally()) target="_blank" rel="noopener noreferrer" @endif
                                        class="{{ $button->styleClasses() }}"
                                    >
                                        {{ $button->localized('label') }}
                                    </a>
                                @endforeach
                            </div>

                            <p data-lc-key="trading_accounts_hint" class="mt-4 text-sm text-gray-500 dark:text-graphite-400">
                                {{ $content['trading_accounts_hint'] }}
                            </p>
                        </div>

                        <div class="relative">
                            <div class="absolute -top-10 -right-8 h-40 w-40 rounded-full bg-brand-100/80 blur-3xl dark:bg-brand-800/30"></div>
                            <div class="absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-sky-100/80 blur-3xl dark:bg-sky-800/20"></div>
                            <div class="relative flex flex-col items-center justify-center px-4 py-6 sm:px-6 sm:py-8">
                                <img data-lc-key="hero_image_light" src="{{ asset('storage/'.$content['hero_image_light']) }}" alt="AET Trader Academy logo" class="mx-auto block w-full max-w-[280px] dark:hidden">
                                <img data-lc-key="hero_image_dark" src="{{ asset('storage/'.$content['hero_image_dark']) }}" alt="AET Trader Academy logo dark" class="mx-auto hidden w-full max-w-[280px] dark:block">
                                <div class="mt-5 text-center max-w-sm">
                                    <p class="text-sm font-semibold tracking-wide text-gray-900 dark:text-graphite-100">AET Trader Academy</p>
                                    <p data-lc-key="hero_logo_caption" class="mt-1 text-sm text-gray-600 dark:text-graphite-300">{{ $content['hero_logo_caption'] }}</p>
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
                                <span data-lc-key="programs_badge" class="program-fade-up inline-flex items-center rounded-full border border-white/15 bg-white/8 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-brand-100 backdrop-blur" style="animation-delay: 80ms;">
                                    {{ $content['programs_badge'] }}
                                </span>

                                <h2 data-lc-key="programs_title" class="program-fade-up mt-5 max-w-3xl text-3xl font-extrabold tracking-tight text-white sm:text-4xl" style="animation-delay: 160ms;">
                                    {{ $content['programs_title'] }}
                                </h2>

                                <p data-lc-key="programs_description" class="program-fade-up mt-4 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base" style="animation-delay: 240ms;">
                                    {{ $content['programs_description'] }}
                                </p>

                                <div class="program-fade-up mt-6 flex flex-wrap gap-3" style="animation-delay: 320ms;">
                                    <span data-lc-key="programs_pill_1" class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ $content['programs_pill_1'] }}</span>
                                    <span data-lc-key="programs_pill_2" class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ $content['programs_pill_2'] }}</span>
                                    <span data-lc-key="programs_pill_3" class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ $content['programs_pill_3'] }}</span>
                                    <span data-lc-key="programs_pill_4" class="inline-flex items-center rounded-full border border-white/12 bg-white/8 px-3 py-1 text-xs font-medium text-slate-100">{{ $content['programs_pill_4'] }}</span>
                                </div>

                                <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
                                    <article class="program-fade-up rounded-[1.6rem] border border-white/12 bg-white/8 p-5 shadow-[0_20px_50px_-34px_rgba(15,23,42,0.95)] backdrop-blur-md" style="animation-delay: 400ms;">
                                        <p data-lc-key="program_card_1_eyebrow" class="text-xs font-semibold uppercase tracking-[0.24em] text-brand-200">{{ $content['program_card_1_eyebrow'] }}</p>
                                        <h3 data-lc-key="program_card_1_title" class="mt-3 text-xl font-semibold text-white">{{ $content['program_card_1_title'] }}</h3>
                                        <p data-lc-key="program_card_1_desc" class="mt-3 text-sm leading-6 text-slate-300">{{ $content['program_card_1_desc'] }}</p>
                                        <div class="mt-6 grid gap-3">
                                            <div data-lc-key="program_card_1_item_1_title" class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm font-medium text-white">{{ $content['program_card_1_item_1_title'] }}</div>
                                            <div data-lc-key="program_card_1_item_2_title" class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm font-medium text-white">{{ $content['program_card_1_item_2_title'] }}</div>
                                            <div data-lc-key="program_card_1_item_3_title" class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm font-medium text-white">{{ $content['program_card_1_item_3_title'] }}</div>
                                        </div>
                                    </article>

                                    <article class="program-fade-up rounded-[1.6rem] border border-white/12 bg-slate-950/40 p-5 shadow-[0_20px_50px_-34px_rgba(15,23,42,0.95)] backdrop-blur-md" style="animation-delay: 480ms;">
                                        <p data-lc-key="program_card_2_eyebrow" class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-200">{{ $content['program_card_2_eyebrow'] }}</p>
                                        <h3 data-lc-key="program_card_2_title" class="mt-3 text-xl font-semibold text-white">{{ $content['program_card_2_title'] }}</h3>
                                        <p data-lc-key="program_card_2_desc" class="mt-3 text-sm leading-6 text-slate-300">{{ $content['program_card_2_desc'] }}</p>

                                        <div class="mt-6 space-y-3">
                                            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                                                <p data-lc-key="program_card_2_item_1_title" class="text-sm font-semibold text-white">{{ $content['program_card_2_item_1_title'] }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                                                <p data-lc-key="program_card_2_item_2_title" class="text-sm font-semibold text-white">{{ $content['program_card_2_item_2_title'] }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-white/10 bg-white/6 px-4 py-3">
                                                <p data-lc-key="program_card_2_item_3_title" class="text-sm font-semibold text-white">{{ $content['program_card_2_item_3_title'] }}</p>
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
                                        <img src="{{ asset('storage/aet-logo-dark.png') }}" alt="AET Trader Academy" class="h-12 w-12 object-contain">
                                    </div>

                                    <div>
                                        <p data-lc-key="programs_panel_badge" class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">{{ $content['programs_panel_badge'] }}</p>
                                        <p class="mt-2 text-2xl font-semibold tracking-tight text-white">AET Trader Academy</p>
                                    </div>
                                </div>

                                <p data-lc-key="programs_panel_title" class="mt-6 text-lg font-semibold leading-8 text-white">
                                    {{ $content['programs_panel_title'] }}
                                </p>
                                <p data-lc-key="programs_panel_description" class="mt-3 text-sm leading-7 text-slate-300">
                                    {{ $content['programs_panel_description'] }}
                                </p>
                                <p data-lc-key="programs_premium_schedule" class="mt-3 rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-sm font-medium leading-6 text-emerald-200">
                                    {{ $content['programs_premium_schedule'] }}
                                </p>

                                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3 xl:grid-cols-1">
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-4">
                                        <p data-lc-key="programs_stat_1_label" class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ $content['programs_stat_1_label'] }}</p>
                                        <p data-lc-key="programs_stat_1_value" class="mt-2 text-sm font-semibold text-white">{{ $content['programs_stat_1_value'] }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-4">
                                        <p data-lc-key="programs_stat_2_label" class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ $content['programs_stat_2_label'] }}</p>
                                        <p data-lc-key="programs_stat_2_value" class="mt-2 text-sm font-semibold text-white">{{ $content['programs_stat_2_value'] }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-4">
                                        <p data-lc-key="programs_stat_3_label" class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ $content['programs_stat_3_label'] }}</p>
                                        <p data-lc-key="programs_stat_3_value" class="mt-2 text-sm font-semibold text-white">{{ $content['programs_stat_3_value'] }}</p>
                                    </div>
                                </div>

                                <a data-lc-key="programs_cta" href="#contacto" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:ring-offset-2 focus:ring-offset-slate-950">
                                    {{ $content['programs_cta'] }}
                                </a>
                            </aside>
                        </div>
                    </div>
                </section>

                <section id="quien-detras" class="mt-10 sm:mt-14 rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:gap-8 items-start">
                        <div>
                            <h2 data-lc-key="behind_title" class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ $content['behind_title'] }}
                            </h2>
                            <p data-lc-key="behind_description" class="mt-3 text-sm sm:text-base text-gray-600 dark:text-graphite-300 leading-relaxed">
                                {{ $content['behind_description'] }}
                            </p>

                            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="rounded-xl border border-gray-200 dark:border-graphite-800 px-4 py-3">
                                    <p data-lc-key="behind_focus_1_label" class="text-xs uppercase tracking-wide text-gray-500 dark:text-graphite-400">{{ $content['behind_focus_1_label'] }}</p>
                                    <p data-lc-key="behind_focus_1_value" class="mt-1 text-sm font-medium text-gray-800 dark:text-graphite-100">{{ $content['behind_focus_1_value'] }}</p>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-graphite-800 px-4 py-3">
                                    <p data-lc-key="behind_focus_2_label" class="text-xs uppercase tracking-wide text-gray-500 dark:text-graphite-400">{{ $content['behind_focus_2_label'] }}</p>
                                    <p data-lc-key="behind_focus_2_value" class="mt-1 text-sm font-medium text-gray-800 dark:text-graphite-100">{{ $content['behind_focus_2_value'] }}</p>
                                </div>
                            </div>
                        </div>

                        <aside class="rounded-2xl border border-gray-200 dark:border-graphite-800 bg-gray-50 dark:bg-graphite-800/60 p-5 lg:sticky lg:top-24">
                            <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-graphite-700 bg-white dark:bg-graphite-900">
                                <div class="h-[360px] sm:h-[440px] lg:h-[420px] w-full bg-gray-100 dark:bg-graphite-900">
                                    <img data-lc-key="behind_photo" src="{{ asset('storage/'.$content['behind_photo']) }}" alt="{{ $content['behind_person_name'] }} - AET Trader Academy" class="h-full w-full object-cover object-top">
                                </div>
                            </div>
                            <div class="mt-4">
                                <p data-lc-key="behind_person_name" class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $content['behind_person_name'] }}</p>
                                <p data-lc-key="behind_badge" class="text-xs text-gray-600 dark:text-graphite-300">{{ $content['behind_badge'] }}</p>
                            </div>
                            <p data-lc-key="behind_quote" class="mt-4 text-sm text-gray-600 dark:text-graphite-300">{{ $content['behind_quote'] }}</p>
                        </aside>
                    </div>
                </section>

                <section id="testimonios" class="mt-10 sm:mt-14">
                    <h2 data-lc-key="testimonials_title" class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                        {{ $content['testimonials_title'] }}
                    </h2>
                    <p data-lc-key="testimonials_description" class="mt-2 text-sm sm:text-base text-gray-600 dark:text-graphite-300">
                        {{ $content['testimonials_description'] }}
                    </p>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-5">
                        @foreach ($testimonials as $testimonial)
                            <article data-lc-testimonial="{{ $testimonial->id }}" class="rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                                <div class="aspect-[9/16] bg-gray-100 dark:bg-graphite-800">
                                    <img src="{{ asset('storage/'.$testimonial->photo_path) }}" alt="{{ $testimonial->localized('name') }}" class="h-full w-full object-contain object-center">
                                </div>
                                <div class="p-4">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $testimonial->localized('name') }}</h3>
                                    <p class="mt-1 text-xs text-gray-600 dark:text-graphite-300">{{ $testimonial->localized('quote') }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section id="contacto" class="mt-10 sm:mt-14 mb-6 rounded-[2rem] border border-brand-200 bg-gradient-to-br from-brand-50 via-white to-emerald-50 p-6 sm:p-8 dark:border-brand-900 dark:from-brand-950/30 dark:via-graphite-900 dark:to-emerald-950/20">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.1fr)_420px] lg:gap-10 items-start">
                        <div>
                            <span data-lc-key="contact_badge" class="inline-flex rounded-full border border-brand-200 bg-white/80 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 shadow-sm dark:border-brand-900 dark:bg-brand-950/30 dark:text-brand-300">
                                {{ $content['contact_badge'] }}
                            </span>

                            <h2 data-lc-key="contact_title" class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ $content['contact_title'] }}
                            </h2>
                            <p data-lc-key="contact_description" class="mt-2 max-w-2xl text-sm sm:text-base text-gray-700 dark:text-graphite-200 leading-7">
                                {{ $content['contact_description'] }}
                            </p>

                            <div class="mt-6">
                                <div class="space-y-5">
                                    <div class="rounded-[1.6rem] border border-white/60 bg-white/85 p-5 shadow-[0_18px_50px_-34px_rgba(15,23,42,0.75)] backdrop-blur dark:border-graphite-700 dark:bg-graphite-900/85">
                                        <p data-lc-key="contact_new_eyebrow" class="text-xs font-semibold uppercase tracking-[0.22em] text-brand-700 dark:text-brand-300">{{ $content['contact_new_eyebrow'] }}</p>
                                        <h3 data-lc-key="contact_new_title" class="mt-3 text-xl font-semibold text-gray-900 dark:text-graphite-100">{{ $content['contact_new_title'] }}</h3>
                                        <p data-lc-key="contact_new_description" class="mt-3 text-sm leading-7 text-gray-600 dark:text-graphite-300">{{ $content['contact_new_description'] }}</p>

                                        <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-graphite-800 dark:bg-graphite-950/60">
                                                <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">01</p>
                                                <p data-lc-key="contact_new_step_1" class="mt-2 text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $content['contact_new_step_1'] }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-graphite-800 dark:bg-graphite-950/60">
                                                <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">02</p>
                                                <p data-lc-key="contact_new_step_2" class="mt-2 text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $content['contact_new_step_2'] }}</p>
                                            </div>
                                            <div class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-graphite-800 dark:bg-graphite-950/60">
                                                <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">03</p>
                                                <p data-lc-key="contact_new_step_3" class="mt-2 text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $content['contact_new_step_3'] }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <a data-lc-key="contact_telegram_url contact_telegram_handle_display" href="{{ $content['contact_telegram_url'] }}" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm4.64 7.06-1.65 7.79c-.12.56-.45.69-.92.43l-2.54-1.87-1.22 1.17c-.13.13-.24.24-.5.24l.18-2.6 4.73-4.27c.21-.18-.04-.29-.32-.11l-5.85 3.68-2.52-.79c-.55-.17-.56-.55.11-.81l9.84-3.79c.46-.17.86.11.66 1.03Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Telegram</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">{{ $content['contact_telegram_handle_display'] }}</p>
                                            </div>
                                        </a>

                                        <a data-lc-key="contact_instagram_url contact_instagram_handle_display" href="{{ $content['contact_instagram_url'] }}" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm8.5 1.5h-8.5A4.25 4.25 0 0 0 3.5 7.75v8.5a4.25 4.25 0 0 0 4.25 4.25h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5a4.25 4.25 0 0 0-4.25-4.25ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm5.25-.88a1.13 1.13 0 1 1 0 2.26 1.13 1.13 0 0 1 0-2.26Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">Instagram</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">{{ $content['contact_instagram_handle_display'] }}</p>
                                            </div>
                                        </a>

                                        <a data-lc-key="contact_email" href="mailto:{{ $content['contact_email'] }}" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M3 5.75A2.75 2.75 0 0 1 5.75 3h12.5A2.75 2.75 0 0 1 21 5.75v12.5A2.75 2.75 0 0 1 18.25 21H5.75A2.75 2.75 0 0 1 3 18.25V5.75Zm2.1-.89 6.38 5.1a.83.83 0 0 0 1.04 0l6.38-5.1a1.25 1.25 0 0 0-.65-.18H5.75c-.23 0-.45.06-.65.18Zm14.4 2.03-5.96 4.77a2.33 2.33 0 0 1-2.9 0L4.7 6.89v11.36c0 .69.56 1.25 1.25 1.25h12.1c.69 0 1.25-.56 1.25-1.25V6.89Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.email') }}</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">{{ $content['contact_email'] }}</p>
                                            </div>
                                        </a>

                                        <a data-lc-key="contact_whatsapp_number_raw contact_whatsapp_number_display" href="https://wa.me/{{ $content['contact_whatsapp_number_raw'] }}" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 transition hover:border-brand-400 hover:bg-brand-50 dark:border-graphite-700 dark:bg-graphite-900 dark:hover:border-brand-700 dark:hover:bg-brand-950/20">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true"><path d="M12.04 2.5c-5.23 0-9.47 4.18-9.47 9.35 0 1.66.44 3.28 1.27 4.7L2.5 21.5l5.08-1.31a9.55 9.55 0 0 0 4.46 1.12h.01c5.23 0 9.47-4.18 9.47-9.35 0-2.49-.99-4.83-2.77-6.59A9.52 9.52 0 0 0 12.04 2.5Zm0 17.2h-.01a7.92 7.92 0 0 1-4.03-1.1l-.29-.17-3.02.78.81-2.93-.19-.3a7.8 7.8 0 0 1-1.23-4.12c0-4.31 3.55-7.82 7.95-7.82 2.12 0 4.11.82 5.61 2.31a7.72 7.72 0 0 1 2.34 5.52c0 4.31-3.56 7.82-7.94 7.82Zm4.35-5.89c-.24-.12-1.4-.69-1.62-.77-.22-.08-.38-.12-.54.12-.16.23-.62.77-.76.92-.14.16-.28.18-.52.06-.24-.12-1-.37-1.9-1.19-.7-.62-1.17-1.39-1.31-1.62-.14-.23-.01-.35.1-.47.1-.1.24-.27.36-.4.12-.14.16-.23.24-.39.08-.16.04-.29-.02-.41-.06-.12-.54-1.29-.74-1.76-.2-.47-.4-.39-.54-.39h-.47c-.16 0-.41.06-.62.29-.22.23-.84.81-.84 1.99s.86 2.3.98 2.46c.12.16 1.68 2.66 4.14 3.63.58.23 1.04.37 1.39.47.58.16 1.1.14 1.52.08.46-.07 1.4-.57 1.6-1.12.2-.55.2-1.02.14-1.12-.06-.1-.22-.16-.46-.27Z"/></svg>
                                            </span>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">WhatsApp</p>
                                                <p class="text-xs text-gray-600 dark:text-graphite-300">{{ $content['contact_whatsapp_number_display'] }}</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="relative overflow-hidden rounded-[1.8rem] border border-gray-200 bg-white p-4 sm:p-5 shadow-sm dark:border-graphite-700 dark:bg-graphite-900">
                            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-brand-100/70 blur-3xl dark:bg-brand-700/20"></div>

                            <div class="relative">
                                <p data-lc-key="contact_qr_title" class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $content['contact_qr_title'] }}</p>
                                <p data-lc-key="contact_qr_description" class="mt-1 text-xs leading-6 text-gray-600 dark:text-graphite-300">{{ $content['contact_qr_description'] }}</p>

                                <div class="mt-4 rounded-xl overflow-hidden border border-gray-200 dark:border-graphite-700 bg-gray-50 dark:bg-graphite-800">
                                    <img data-lc-key="contact_qr_image contact_qr_alt" src="{{ asset('storage/'.$content['contact_qr_image']) }}" alt="{{ $content['contact_qr_alt'] }}" class="w-full h-auto object-contain">
                                </div>

                                <a data-lc-key="contact_qr_button" href="{{ $content['contact_telegram_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center justify-center w-full px-4 py-3 rounded-xl bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ $content['contact_qr_button'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-gray-200 bg-white dark:bg-graphite-900 dark:border-graphite-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-graphite-400">
                    <p data-lc-key="footer_copyright_suffix">&copy; {{ date('Y') }} {{ $content['footer_copyright_suffix'] }}</p>

                    <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                        <a href="{{ route('legal.privacy') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.link.privacy_policy') }}</a>
                        <a href="{{ route('legal.terms') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.link.terms_and_conditions') }}</a>
                        <a data-lc-key="contact_email" href="mailto:{{ $content['contact_email'] }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $content['contact_email'] }}</a>
                        <a data-lc-key="contact_whatsapp_number_raw contact_whatsapp_number_display" href="https://wa.me/{{ $content['contact_whatsapp_number_raw'] }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $content['contact_whatsapp_number_display'] }}</a>
                    </nav>
                </div>
            </footer>
        </div>

        @if ($isPreview ?? false)
            <script>
                (function () {
                    function clearHighlights() {
                        document.querySelectorAll('.lc-preview-highlight').forEach((el) => el.classList.remove('lc-preview-highlight'));
                    }

                    function ensureTabVisible(el) {
                        const tabContainer = el.closest('[data-lc-tab]');
                        if (tabContainer && tabContainer.offsetParent === null) {
                            const tabName = tabContainer.getAttribute('data-lc-tab');
                            const btn = document.querySelector('[data-lc-tab-button="' + tabName + '"]');
                            if (btn) {
                                btn.click();
                                return true;
                            }
                        }
                        return false;
                    }

                    // Scroll only this window's own document. Element.scrollIntoView()
                    // walks every ancestor scroll container, including across the
                    // iframe boundary into the parent admin page - which would drag
                    // the admin's own layout out from under the pointer. Scrolling
                    // window/document directly keeps the effect confined to this frame.
                    function scrollWithinFrame(el) {
                        const rect = el.getBoundingClientRect();
                        const targetTop = window.scrollY + rect.top - (window.innerHeight / 2) + (rect.height / 2);
                        window.scrollTo({ top: Math.max(0, targetTop), behavior: 'smooth' });
                    }

                    function highlight(key) {
                        clearHighlights();
                        const safeKey = CSS.escape(key);
                        const el = document.querySelector('[data-lc-key~="' + safeKey + '"]')
                            || document.querySelector('[data-lc-testimonial="' + safeKey + '"]')
                            || document.querySelector('[data-lc-hero-button="' + safeKey + '"]');
                        if (!el) return;

                        const switchedTab = ensureTabVisible(el);
                        const doHighlight = function () {
                            el.classList.add('lc-preview-highlight');
                            scrollWithinFrame(el);
                        };

                        if (switchedTab) {
                            setTimeout(doHighlight, 80);
                        } else {
                            doHighlight();
                        }
                    }

                    window.addEventListener('message', function (event) {
                        if (event.origin !== window.location.origin) return;
                        if (!event.data || typeof event.data !== 'object') return;
                        if (event.data.source !== 'lc-admin-preview') return;

                        if (event.data.type === 'highlight' && event.data.key) {
                            highlight(String(event.data.key));
                        } else if (event.data.type === 'clear') {
                            clearHighlights();
                        }
                    });
                })();
            </script>
        @endif
    </body>
</html>
