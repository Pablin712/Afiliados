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
            </div>

            <div
                x-data="{
                    search: '',
                    templates: {{ json_encode($templates->map(fn ($t) => ['id' => $t->id, 'searchable' => strtolower($t->name.' '.$t->key.' '.$t->description)])) }},
                    isVisible(id) {
                        const q = this.search.trim().toLowerCase();
                        if (q === '') { return true; }
                        const t = this.templates.find(t => t.id === id);
                        return t ? t.searchable.includes(q) : false;
                    },
                    hasVisible() {
                        return this.templates.some(t => this.isVisible(t.id));
                    },
                }"
                class="space-y-4"
            >
                <div class="sticky top-0 z-10 -mx-4 px-4 py-2 sm:mx-0 sm:px-0 sm:py-0 bg-gray-50/95 dark:bg-graphite-950/95 backdrop-blur sm:bg-transparent sm:backdrop-blur-none">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M18 11a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/>
                        </svg>
                        <input
                            type="search"
                            x-model="search"
                            placeholder="{{ __('messages.admin.message_templates.search_placeholder') }}"
                            class="w-full rounded-md border-gray-300 pl-9 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"
                        >
                    </div>
                </div>

                <p x-show="! hasVisible()" x-cloak class="rounded-md border border-dashed border-gray-300 dark:border-graphite-700 px-4 py-6 text-center text-sm text-gray-500 dark:text-graphite-400">
                    {{ __('messages.admin.message_templates.no_matches') }}
                </p>

                <div class="space-y-3">
                    @foreach ($templates as $template)
                        <div
                            x-data="{ open: false }"
                            x-show="isVisible({{ $template->id }})"
                            class="bg-white dark:bg-graphite-900 rounded-lg shadow border border-gray-200 dark:border-graphite-700 overflow-hidden"
                        >
                            <div
                                role="button"
                                tabindex="0"
                                @click="open = ! open"
                                @keydown.enter="open = ! open"
                                @keydown.space.prevent="open = ! open"
                                :aria-expanded="open"
                                class="px-5 py-4 flex items-start justify-between gap-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-brand-500"
                            >
                                <div class="min-w-0 flex items-start gap-3">
                                    <svg
                                        class="h-5 w-5 mt-0.5 shrink-0 text-gray-400 transition-transform duration-200"
                                        :class="open ? 'rotate-90' : ''"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-base font-semibold text-gray-800 dark:text-graphite-100">{{ $template->name }}</p>
                                        <p class="mt-0.5 text-xs font-mono text-gray-400 dark:text-graphite-500">{{ $template->key }}</p>
                                        @if ($template->description)
                                            <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ $template->description }}</p>
                                        @endif
                                    </div>
                                </div>

                                <x-primary-button
                                    type="button"
                                    class="shrink-0"
                                    x-on:click.stop="window.openTemplateEditModal({{ json_encode(['id' => $template->id, 'name' => $template->name, 'body' => $template->body]) }})"
                                >
                                    {{ __('messages.edit') }}
                                </x-primary-button>
                            </div>

                            <div class="grid transition-[grid-template-rows] duration-300 ease-in-out" :class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
                                <div class="overflow-hidden">
                                    <div class="border-t border-gray-100 dark:border-graphite-800 px-5 py-4 bg-gray-50 dark:bg-graphite-950">
                                        <pre class="max-h-96 overflow-y-auto whitespace-pre-wrap text-sm text-gray-700 dark:text-graphite-300 font-sans leading-relaxed">{{ $template->body }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
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
