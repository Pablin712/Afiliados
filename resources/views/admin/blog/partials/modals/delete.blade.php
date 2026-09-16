@can('edit landing_content')
    <x-modal name="blog-post-delete-modal" focusable>
        <form id="blog-post-delete-form" method="POST" action="{{ route('admin.blog.destroy', ['blogPost' => '__ID__']) }}" class="p-6 space-y-4">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.blog.forms.delete_title') }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-graphite-300">
                {{ __('messages.admin.blog.messages.confirm_delete') }}
                <span id="blog-post-delete-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
            </p>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'blog-post-delete-modal')">{{ __('messages.admin.blog.buttons.cancel') }}</x-secondary-button>
                <x-danger-button>{{ __('messages.admin.blog.buttons.delete') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
@endcan
