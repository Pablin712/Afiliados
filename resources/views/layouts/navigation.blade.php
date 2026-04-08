@php($canAccessCourses = Auth::user()?->hasRole('admin') || strtolower((string) (Auth::user()?->membership?->membershipType?->name ?? 'free')) !== 'free')

<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 dark:bg-graphite-900 dark:border-graphite-800">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-10 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('messages.nav.dashboard') }}
                    </x-nav-link>
                    @role('user')
                        <x-nav-link :href="route('user.network.index')" :active="request()->routeIs('user.network.*')">
                            {{ __('messages.nav.my_network') }}
                        </x-nav-link>
                        <x-nav-link :href="route('user.profits.index')" :active="request()->routeIs('user.profits.*')">
                            {{ __('messages.nav.my_profits') }}
                        </x-nav-link>
                    @endrole
                    @if ($canAccessCourses)
                        <x-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">
                            {{ __('messages.nav.courses') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('plans.index')" :active="request()->routeIs('plans.*')">
                        {{ __('messages.nav.plans') }}
                    </x-nav-link>
                    <x-nav-link :href="url('/')" :active="request()->is('/')">
                        {{ __('messages.nav.main_site') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-4">
                <!-- Theme Toggle Button -->
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

                <!-- Language Switcher -->
                <div class="border-l border-gray-300 dark:border-graphite-700 pl-4">
                    <x-language-switcher />
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-brand-600 dark:text-graphite-300 dark:bg-graphite-900 dark:hover:text-brand-400 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('messages.profile') }}
                        </x-dropdown-link>

                        @can('view actions')
                            <x-dropdown-link :href="route('actions.index')">
                                {{ __('messages.audit.title') }}
                            </x-dropdown-link>
                        @endcan

                        @can('view memberships')
                            <x-dropdown-link :href="route('memberships.index')">
                                {{ __('memberships.title') }}
                            </x-dropdown-link>
                        @endcan

                        @can('manage payments')
                            <x-dropdown-link :href="route('admin.pending-registrations.index')">
                                {{ __('messages.admin.pending_registrations_title') }}
                            </x-dropdown-link>
                        @endcan

                        @can('view users')
                            <x-dropdown-link :href="route('admin.users-tree.index')">
                                {{ __('messages.admin.users_tree.title') }}
                            </x-dropdown-link>
                        @endcan

                        @can('view profits')
                            <x-dropdown-link :href="route('admin.profits.index')">
                                {{ __('messages.admin.profits.title') }}
                            </x-dropdown-link>
                        @endcan

                        @can('view banks')
                            <x-dropdown-link :href="route('admin.banks.index')">
                                {{ __('messages.admin.banks.title') }}
                            </x-dropdown-link>
                        @endcan

                        @role('admin')
                            <x-dropdown-link :href="route('admin.courses.index')">
                                {{ __('messages.admin.courses.title') }}
                            </x-dropdown-link>
                        @endrole

                        @can('report profits')
                            <x-dropdown-link :href="route('admin.financial-dashboard.index')">
                                {{ __('messages.admin.financial_dashboard.title') }}
                            </x-dropdown-link>
                        @endcan

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('messages.log_out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-brand-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-brand-600 dark:text-graphite-400 dark:hover:text-brand-400 dark:hover:bg-graphite-800 dark:focus:bg-graphite-800 dark:focus:text-brand-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('messages.nav.dashboard') }}
            </x-responsive-nav-link>
            @role('user')
                <x-responsive-nav-link :href="route('user.network.index')" :active="request()->routeIs('user.network.*')">
                    {{ __('messages.nav.my_network') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.profits.index')" :active="request()->routeIs('user.profits.*')">
                    {{ __('messages.nav.my_profits') }}
                </x-responsive-nav-link>
            @endrole
            @if ($canAccessCourses)
                <x-responsive-nav-link :href="route('courses.index')" :active="request()->routeIs('courses.*')">
                    {{ __('messages.nav.courses') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('plans.index')" :active="request()->routeIs('plans.*')">
                {{ __('messages.nav.plans') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="url('/')" :active="request()->is('/')">
                {{ __('messages.nav.main_site') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-graphite-800">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-graphite-100">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-graphite-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 px-4">
                <button
                    type="button"
                    class="w-full inline-flex items-center justify-center px-3 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:text-brand-400"
                    x-on:click="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                >
                    <span class="text-sm">{{ __('messages.nav.change_theme') }}</span>
                </button>
            </div>

            <!-- Language Switcher Mobile -->
            <div class="mt-3 px-4">
                <div class="flex justify-center gap-2">
                    <x-language-switcher />
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('messages.profile') }}
                </x-responsive-nav-link>

                @can('view actions')
                    <x-responsive-nav-link :href="route('actions.index')">
                        {{ __('messages.audit.title') }}
                    </x-responsive-nav-link>
                @endcan

                @can('view memberships')
                    <x-responsive-nav-link :href="route('memberships.index')">
                        {{ __('memberships.title') }}
                    </x-responsive-nav-link>
                @endcan

                @can('manage payments')
                    <x-responsive-nav-link :href="route('admin.pending-registrations.index')">
                        {{ __('messages.admin.pending_registrations_title') }}
                    </x-responsive-nav-link>
                @endcan

                @can('view users')
                    <x-responsive-nav-link :href="route('admin.users-tree.index')">
                        {{ __('messages.admin.users_tree.title') }}
                    </x-responsive-nav-link>
                @endcan

                @can('view profits')
                    <x-responsive-nav-link :href="route('admin.profits.index')">
                        {{ __('messages.admin.profits.title') }}
                    </x-responsive-nav-link>
                @endcan

                @can('view banks')
                    <x-responsive-nav-link :href="route('admin.banks.index')">
                        {{ __('messages.admin.banks.title') }}
                    </x-responsive-nav-link>
                @endcan

                @role('admin')
                    <x-responsive-nav-link :href="route('admin.courses.index')">
                        {{ __('messages.admin.courses.title') }}
                    </x-responsive-nav-link>
                @endrole

                @can('report profits')
                    <x-responsive-nav-link :href="route('admin.financial-dashboard.index')">
                        {{ __('messages.admin.financial_dashboard.title') }}
                    </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('messages.log_out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
