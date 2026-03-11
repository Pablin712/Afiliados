<section>
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

        <x-text-input
            id="affiliate_link"
            type="text"
            class="mt-1 block w-full"
            :value="route('register') . '?ref=' . urlencode($user->currentAffiliateCode())"
            readonly
        />

        <p class="mt-2 text-sm text-gray-600 dark:text-graphite-400">
            {{ __('messages.profile.referral_link_help') }}
        </p>
    </div>
</section>
