<x-guest-layout>
    <div class="mb-6 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/15">
            <svg class="h-7 w-7 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-graphite-100">
            {{ __('messages.auth.device.conflict_title') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.auth.device.conflict_subtitle') }}
        </p>
    </div>

    {{-- Active device card --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-graphite-700 dark:bg-graphite-800/50">
        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-graphite-400">
            {{ __('messages.auth.device.active_device') }}
        </p>
        <div class="grid gap-2 text-sm">
            <div class="flex items-center gap-2 text-gray-700 dark:text-graphite-300">
                <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/>
                </svg>
                <span><span class="font-medium">{{ $browser }}</span> &middot; {{ $os }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-700 dark:text-graphite-300">
                <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253"/>
                </svg>
                <span>{{ $ip }}</span>
            </div>
            <div class="flex items-center gap-2 text-gray-700 dark:text-graphite-300">
                <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/>
                </svg>
                <span>{{ __('messages.auth.device.last_activity') }}: {{ $lastActivity->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
        <form method="POST" action="{{ route('auth.device-conflict.takeover') }}">
            @csrf
            <button type="submit"
                class="w-full rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:bg-brand-500 dark:hover:bg-brand-400">
                {{ __('messages.auth.device.takeover_button') }}
            </button>
        </form>

        <a href="{{ route('auth.device-conflict.cancel') }}"
            class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-graphite-600">
            {{ __('messages.auth.device.cancel_button') }}
        </a>
    </div>

    <p class="mt-5 text-center text-xs text-gray-400 dark:text-graphite-500">
        {{ __('messages.auth.device.conflict_note') }}
    </p>
</x-guest-layout>
