<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-graphite-100">
            {{ __('messages.profile.telegram_title') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
            {{ __('messages.profile.telegram_description') }}
        </p>
    </header>

    @if (session('telegram_required'))
        <div class="mt-4 rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
            {{ session('telegram_required') }}
        </div>
    @endif

    @if (session('status') === 'telegram-chat-id-removed')
        <div class="mt-4 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
            {{ __('messages.profile.telegram_removed') }}
        </div>
    @endif

    <div class="mt-6">
        @if ($user->telegram_chat_id)
            {{-- Already linked --}}
            <div class="rounded-md border border-green-300 bg-green-50 px-4 py-4 dark:border-green-700 dark:bg-green-900/20">
                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                    ✅ {{ __('messages.profile.telegram_registered_label') }}
                </p>
                <p class="mt-1 text-xs text-green-700 dark:text-green-400">
                    {{ __('messages.profile.telegram_registered_hint') }}
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-graphite-400">
                    Chat ID: <span class="font-mono">{{ $user->telegram_chat_id }}</span>
                </p>
            </div>

            <form method="POST" action="{{ route('profile.telegram-chat-id.destroy') }}" class="mt-3"
                  onsubmit="return confirm('{{ __('messages.profile.telegram_remove_button') }}?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-50 dark:border-red-700 dark:bg-graphite-800 dark:text-red-400 dark:hover:bg-graphite-700">
                    {{ __('messages.profile.telegram_remove_button') }}
                </button>
            </form>
        @else
            {{-- Not linked — show code and bot link --}}
            <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-4 dark:border-graphite-700 dark:bg-graphite-800">
                <p class="text-sm text-gray-700 dark:text-graphite-300">
                    {{ __('messages.profile.telegram_code_hint') }}
                </p>

                <div class="mt-3 flex items-center gap-3">
                    <span id="telegram-code"
                          class="rounded-md border border-gray-300 bg-white px-3 py-2 font-mono text-lg font-bold tracking-widest text-gray-900 select-all dark:border-graphite-600 dark:bg-graphite-900 dark:text-graphite-100">
                        {{ $user->telegram_code }}
                    </span>

                    <button type="button" id="copy-telegram-code"
                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-graphite-600 dark:bg-graphite-800 dark:text-graphite-300 dark:hover:bg-graphite-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span id="copy-telegram-label">{{ __('messages.profile.telegram_copy') }}</span>
                    </button>
                </div>

                <div class="mt-4">
                    <a href="https://t.me/aetfirstbot" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-md bg-[#229ED9] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#1a86bb] focus:outline-none focus:ring-2 focus:ring-[#229ED9] focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248-2.04 9.613c-.153.68-.553.847-1.12.527l-3.1-2.285-1.496 1.44c-.165.165-.304.304-.624.304l.223-3.168 5.754-5.197c.25-.222-.054-.346-.386-.124l-7.11 4.478-3.063-.957c-.666-.208-.68-.666.138-.986l11.97-4.616c.553-.2 1.037.136.854.97z"/>
                        </svg>
                        Abrir @aetfirstbot
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
(function () {
    const btn   = document.getElementById('copy-telegram-code');
    const code  = document.getElementById('telegram-code');
    const label = document.getElementById('copy-telegram-label');
    if (!btn || !code) return;

    btn.addEventListener('click', function () {
        navigator.clipboard.writeText(code.textContent.trim()).then(function () {
            label.textContent = @js(__('messages.profile.telegram_copied'));
            setTimeout(function () {
                label.textContent = @js(__('messages.profile.telegram_copy'));
            }, 2000);
        });
    });
})();
</script>
@endpush
