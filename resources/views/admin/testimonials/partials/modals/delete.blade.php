@can('edit landing_content')
    <x-modal name="testimonial-delete-modal" focusable>
        <form id="testimonial-delete-form" method="POST" action="{{ route('admin.testimonials.destroy', ['testimonial' => '__ID__']) }}" class="p-6 space-y-4">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.testimonials.forms.delete_title') }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-graphite-300">
                {{ __('messages.admin.testimonials.messages.confirm_delete_modal') }}
                <span id="testimonial-delete-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
            </p>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'testimonial-delete-modal')">{{ __('messages.admin.testimonials.buttons.cancel') }}</x-secondary-button>
                <x-danger-button>{{ __('messages.admin.testimonials.buttons.delete') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
@endcan
