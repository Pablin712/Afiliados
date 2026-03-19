<section
    x-data="{
        copied: false,
        async copyReferralLink() {
            const input = document.getElementById('affiliate_link');
            if (!input) return;

            const value = input.value;

            try {
                await navigator.clipboard.writeText(value);
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
            {{ __('messages.profile.referral_title') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
            {{ __('messages.profile.referral_description') }}
        </p>
    </header>

    <div class="mt-4">
        <x-input-label for="affiliate_link" :value="__('messages.profile.referral_link_label')" />

        <div class="mt-1 flex items-stretch gap-2">
            <x-text-input
                id="affiliate_link"
                type="text"
                class="block w-full"
                :value="route('register') . '?ref=' . urlencode($user->currentAffiliateCode())"
                readonly
            />

            <button
                type="button"
                @click="copyReferralLink"
                :class="copied
                    ? 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-300 dark:bg-emerald-600 dark:hover:bg-emerald-500'
                    : 'bg-brand-600 hover:bg-brand-700 focus:ring-brand-300 dark:bg-brand-700 dark:hover:bg-brand-600'"
                class="inline-flex min-w-[132px] items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-graphite-950"
                :aria-label="copied ? '{{ __('messages.profile.referral_copied') }}' : '{{ __('messages.profile.referral_copy') }}'"
            >
                <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path d="M16 1H4a2 2 0 0 0-2 2v12h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z" />
                </svg>
                <svg x-show="copied" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M2.25 12a9.75 9.75 0 1 1 19.5 0 9.75 9.75 0 0 1-19.5 0Zm14.03-2.28a.75.75 0 0 0-1.06-1.06l-4.72 4.72-1.72-1.72a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.06 0l5.25-5.25Z" clip-rule="evenodd" />
                </svg>

                <span x-show="!copied">{{ __('messages.profile.referral_copy') }}</span>
                <span x-show="copied">{{ __('messages.profile.referral_copied') }}</span>
            </button>
        </div>

        <p x-show="copied" x-transition class="mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-400">
            {{ __('messages.profile.referral_copied_help') }}
        </p>

        <p class="mt-2 text-sm text-gray-600 dark:text-graphite-400">
            {{ __('messages.profile.referral_link_help') }}
        </p>
    </div>
</section>
