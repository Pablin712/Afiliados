<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-graphite-100">
            {{ __('messages.profile.other_bank_title') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
            {{ __('messages.profile.other_bank_description') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.other-bank.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="other_bank_name" :value="__('messages.profile.other_bank_name_label')" />
            <x-text-input
                id="other_bank_name"
                name="other_bank_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('other_bank_name', $otherBank?->bank_name)"
                autocomplete="off"
                placeholder="Banco Pichincha, Banco Guayaquil..."
            />
            <x-input-error class="mt-2" :messages="$errors->get('other_bank_name')" />
        </div>

        <div>
            <x-input-label for="other_bank_owner" :value="__('messages.profile.other_bank_owner_label')" />
            <x-text-input
                id="other_bank_owner"
                name="other_bank_owner"
                type="text"
                class="mt-1 block w-full"
                :value="old('other_bank_owner', $otherBank?->owner)"
                autocomplete="off"
            />
            <x-input-error class="mt-2" :messages="$errors->get('other_bank_owner')" />
        </div>

        <div>
            <x-input-label for="other_bank_number" :value="__('messages.profile.other_bank_number_label')" />
            <x-text-input
                id="other_bank_number"
                name="other_bank_number"
                type="text"
                class="mt-1 block w-full"
                :value="old('other_bank_number', $otherBank?->number)"
                autocomplete="off"
            />
            <x-input-error class="mt-2" :messages="$errors->get('other_bank_number')" />
        </div>

        <div>
            <x-input-label for="other_bank_identification" :value="__('messages.profile.other_bank_identification_label')" />
            <x-text-input
                id="other_bank_identification"
                name="other_bank_identification"
                type="text"
                class="mt-1 block w-full"
                :value="old('other_bank_identification', $otherBank?->identification)"
                autocomplete="off"
            />
            <x-input-error class="mt-2" :messages="$errors->get('other_bank_identification')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('messages.profile.other_bank_save_button') }}</x-primary-button>

            @if (session('status') === 'other-bank-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-graphite-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
