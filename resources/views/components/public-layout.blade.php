@props([
    'title' => null,
    'metaDescription' => null,
    'content' => [],
])

{{--
    Chrome shared by the public pages that live outside the one-page landing
    (today: /blog and /blog/{slug}). The header mirrors welcome.blade.php but
    its section links are absolute, since from here "#programas" would point
    at an anchor this page does not have.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? __('messages.blog.title') }} — {{ config('app.name', 'AET SAS') }}</title>
        @if ($metaDescription)
            <meta name="description" content="{{ $metaDescription }}">
        @endif
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
    </head>
    <body x-data="{ mobileMenuOpen: false }" class="font-sans bg-gray-100 text-gray-900 dark:bg-graphite-950 dark:text-graphite-100 antialiased">
        <div class="min-h-screen flex flex-col">
            <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur dark:bg-graphite-900/95 dark:border-graphite-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 shrink-0">
                        <x-application-logo class="h-10 w-auto" />
                        <span class="hidden sm:inline text-sm font-semibold text-gray-800 dark:text-graphite-100">AET SAS</span>
                    </a>

                    <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600 dark:text-graphite-300">
                        <a href="{{ url('/') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.home') }}</a>
                        <a href="{{ url('/#programas') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.programs') }}</a>
                        <a href="{{ route('blog.index') }}" class="text-brand-600 dark:text-brand-400">{{ __('messages.nav.blog') }}</a>
                        <a href="{{ url('/#testimonios') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.testimonials') }}</a>
                        <a href="{{ url('/#contacto') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.nav.contact') }}</a>
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
                            aria-controls="mobile-public-nav"
                        >
                            <span class="sr-only">{{ __('messages.nav.toggle_menu') }}</span>
                            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.nav.dashboard') }}
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-brand-600 text-white text-sm font-semibold hover:bg-brand-500">
                                    {{ __('messages.register') }}
                                </a>
                                <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                                    {{ __('messages.nav.login') }}
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>

                <div id="mobile-public-nav" x-cloak x-show="mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white dark:border-graphite-800 dark:bg-graphite-900">
                    <nav class="px-4 py-3 space-y-2 text-sm font-medium text-gray-700 dark:text-graphite-200">
                        <a href="{{ url('/') }}" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.home') }}</a>
                        <a href="{{ url('/#programas') }}" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.programs') }}</a>
                        <a href="{{ route('blog.index') }}" class="block rounded-md px-3 py-2 bg-brand-50 text-brand-700 dark:bg-brand-950/30 dark:text-brand-300">{{ __('messages.nav.blog') }}</a>
                        <a href="{{ url('/#testimonios') }}" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.testimonials') }}</a>
                        <a href="{{ url('/#contacto') }}" class="block rounded-md px-3 py-2 hover:bg-gray-100 dark:hover:bg-graphite-800">{{ __('messages.nav.contact') }}</a>
                    </nav>
                </div>
            </header>

            <main class="flex-1">
                {{ $slot }}
            </main>

            <footer class="border-t border-gray-200 bg-white dark:bg-graphite-900 dark:border-graphite-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-gray-500 dark:text-graphite-400">
                    <p>&copy; {{ date('Y') }} {{ $content['footer_copyright_suffix'] ?? 'AET SAS' }}</p>

                    <nav class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                        <a href="{{ route('legal.privacy') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.link.privacy_policy') }}</a>
                        <a href="{{ route('legal.terms') }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.link.terms_and_conditions') }}</a>
                        @if (! empty($content['contact_email']))
                            <a href="mailto:{{ $content['contact_email'] }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $content['contact_email'] }}</a>
                        @endif
                        @if (! empty($content['contact_whatsapp_number_raw']))
                            <a href="https://wa.me/{{ $content['contact_whatsapp_number_raw'] }}" class="hover:text-brand-600 dark:hover:text-brand-400">{{ $content['contact_whatsapp_number_display'] ?? 'WhatsApp' }}</a>
                        @endif
                    </nav>
                </div>
            </footer>
        </div>
    </body>
</html>
