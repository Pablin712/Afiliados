<section
    x-data="{
        copied: false,
        async copyCode() {
            const input = document.getElementById('telegram-code-input');
            if (!input) return;
            try {
                await navigator.clipboard.writeText(input.value);
            } catch (e) {
                input.focus();
                input.select();
                document.execCommand('copy');
            }
            this.copied = true;
            window.setTimeout(() => this.copied = false, 1800);
        }
    }"
>
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

    @if ($user->telegram_chat_id)
        {{-- Already linked --}}
        <div class="mt-4 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-800/60 dark:bg-green-900/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-green-800 dark:text-green-300">
                    {{ __('messages.profile.telegram_registered_label') }}
                </p>
                <p class="mt-0.5 text-xs text-green-700 dark:text-green-400">
                    {{ __('messages.profile.telegram_registered_hint') }}
                </p>
                <p class="mt-1.5 font-mono text-xs text-green-700 dark:text-green-500">
                    Chat ID: {{ $user->telegram_chat_id }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.telegram-chat-id.destroy') }}" class="mt-4"
              onsubmit="return confirm('{{ addslashes(__('messages.profile.telegram_remove_button')) }}?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-white px-3 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-2 dark:border-red-700/70 dark:bg-graphite-800 dark:text-red-400 dark:hover:bg-graphite-700 dark:focus:ring-offset-graphite-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
                {{ __('messages.profile.telegram_remove_button') }}
            </button>
        </form>
    @else
        {{-- Not linked — show unique code and bot link --}}
        <div class="mt-4">
            <x-input-label for="telegram-code-input" :value="__('messages.profile.telegram_code_label')" />

            <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
                {{ __('messages.profile.telegram_code_hint') }}
            </p>

            <div class="mt-2 flex items-stretch gap-2">
                <x-text-input
                    id="telegram-code-input"
                    type="text"
                    class="block w-full font-mono text-lg font-bold tracking-widest"
                    :value="$user->telegram_code"
                    readonly
                />

                <button
                    type="button"
                    @click="copyCode"
                    :class="copied
                        ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300 dark:bg-emerald-600 dark:hover:bg-emerald-500'
                        : 'bg-brand-600 hover:bg-brand-700 focus:ring-brand-300 dark:bg-brand-700 dark:hover:bg-brand-600'"
                    class="inline-flex min-w-[120px] items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-graphite-950"
                    :aria-label="copied ? '{{ __('messages.profile.telegram_copied') }}' : '{{ __('messages.profile.telegram_copy') }}'">
                    <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                        <path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z" />
                    </svg>
                    <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.25 12a9.75 9.75 0 1 1 19.5 0 9.75 9.75 0 0 1-19.5 0Zm14.03-2.28a.75.75 0 0 0-1.06-1.06l-4.72 4.72-1.72-1.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l5.25-5.25Z" clip-rule="evenodd" />
                    </svg>
                    <span x-show="!copied">{{ __('messages.profile.telegram_copy') }}</span>
                    <span x-show="copied">{{ __('messages.profile.telegram_copied') }}</span>
                </button>
            </div>

            <p x-show="copied" x-transition class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                {{ __('messages.profile.telegram_copied') }} ✓
            </p>

            <div class="mt-4">
                <a href="https://t.me/aetfirstbot" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-md bg-[#229ED9] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#1a86bb] focus:outline-none focus:ring-2 focus:ring-[#229ED9] focus:ring-offset-2 dark:focus:ring-offset-graphite-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248-2.04 9.613c-.153.68-.553.847-1.12.527l-3.1-2.285-1.496 1.44c-.165.165-.304.304-.624.304l.223-3.168 5.754-5.197c.25-.222-.054-.346-.386-.124l-7.11 4.478-3.063-.957c-.666-.208-.68-.666.138-.986l11.97-4.616c.553-.2 1.037.136.854.97z"/>
                    </svg>
                    Abrir @aetfirstbot
                </a>
            </div>
        </div>
    @endif
</section>
