@can('edit landing_content')
    <x-modal name="blog-post-edit-modal" focusable>
        {{--
            The action is filled in by load() when the modal opens, from the
            route pattern the page script hands over.
        --}}
        <form
            method="POST"
            action="{{ route('admin.blog.update', ['blogPost' => '__ID__']) }}"
            class="p-6 space-y-4 max-h-[85vh] overflow-y-auto"
            enctype="multipart/form-data"
            x-data="blogPostForm(@js(route('admin.blog.update', ['blogPost' => '__ID__'])))"
            x-on:blog-post-edit.window="load($event.detail)"
        >
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.blog.forms.edit_title') }}
            </h3>

            <p class="text-xs text-gray-500 dark:text-graphite-400">
                <a :href="publicUrl" x-show="publicUrl" target="_blank" rel="noopener" class="font-mono text-brand-600 hover:underline dark:text-brand-400" x-text="publicUrl"></a>
            </p>

            @include('admin.blog.partials.form-fields')

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'blog-post-edit-modal')">{{ __('messages.admin.blog.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.blog.buttons.save') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
