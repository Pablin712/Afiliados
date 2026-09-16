@can('edit landing_content')
    <x-modal name="blog-post-create-modal" focusable>
        <form
            method="POST"
            action="{{ route('admin.blog.store') }}"
            class="p-6 space-y-4 max-h-[85vh] overflow-y-auto"
            enctype="multipart/form-data"
            x-data="blogPostForm()"
        >
            @csrf

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.blog.forms.create_title') }}
            </h3>

            @include('admin.blog.partials.form-fields')

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'blog-post-create-modal')">{{ __('messages.admin.blog.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.blog.buttons.create') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
