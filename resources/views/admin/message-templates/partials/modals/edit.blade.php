@role('admin')
    <x-modal name="template-edit-modal" focusable>
        <form id="template-edit-form" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.message_templates.forms.edit_title') }}
            </h3>

            <div>
                <label for="template-edit-name" class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">
                    {{ __('messages.admin.message_templates.columns.name') }}
                </label>
                <input
                    id="template-edit-name"
                    type="text"
                    name="name"
                    required
                    maxlength="150"
                    class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"
                >
            </div>

            <div>
                <label for="template-edit-body" class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">
                    {{ __('messages.admin.message_templates.columns.body') }}
                </label>
                <p class="text-xs text-gray-500 dark:text-graphite-400 mb-1">
                    {{ __('messages.admin.message_templates.variables_hint') }}
                </p>
                <textarea
                    id="template-edit-body"
                    name="body"
                    rows="16"
                    required
                    maxlength="10000"
                    class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono text-sm leading-relaxed"
                ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'template-edit-modal')">
                    {{ __('messages.cancel') }}
                </x-secondary-button>
                <x-primary-button>
                    {{ __('messages.update') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>
@endrole
