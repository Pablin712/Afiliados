<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.admin.message_templates.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                {{ __('messages.admin.message_templates.variables_hint') }}
                <code class="font-mono font-bold">{name}</code>,
                <code class="font-mono font-bold">{email}</code>,
                <code class="font-mono font-bold">{phone}</code>
            </div>

            <div class="space-y-4">
                @foreach ($templates as $template)
                    <div class="bg-white dark:bg-graphite-900 rounded-lg shadow border border-gray-200 dark:border-graphite-700 overflow-hidden">
                        <div class="px-5 py-4 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-base font-semibold text-gray-800 dark:text-graphite-100">{{ $template->name }}</p>
                                @if ($template->description)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ $template->description }}</p>
                                @endif
                            </div>
                            <x-primary-button
                                type="button"
                                class="shrink-0"
                                onclick="window.openTemplateEditModal({{ json_encode(['id' => $template->id, 'name' => $template->name, 'body' => $template->body]) }})"
                            >
                                {{ __('messages.edit') }}
                            </x-primary-button>
                        </div>

                        <div class="border-t border-gray-100 dark:border-graphite-800 px-5 py-4 bg-gray-50 dark:bg-graphite-950">
                            <pre class="whitespace-pre-wrap text-sm text-gray-700 dark:text-graphite-300 font-sans leading-relaxed">{{ $template->body }}</pre>
                        </div>
                    </div>
                @endforeach
            </div>

            @include('admin.message-templates.partials.modals.edit')
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const updatePattern = @json(route('admin.message-templates.update', ['messageTemplate' => '__ID__']));

                    window.openTemplateEditModal = function (payload) {
                        const form = document.getElementById('template-edit-form');
                        if (!form || !payload || !payload.id) return;

                        form.action = updatePattern.replace('__ID__', String(payload.id));
                        document.getElementById('template-edit-name').value = payload.name ?? '';
                        document.getElementById('template-edit-body').value = payload.body ?? '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'template-edit-modal' }));
                    };
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>
