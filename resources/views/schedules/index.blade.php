<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                Horarios de Clases
            </h2>
            @if (auth()->user()->hasRole('teacher') || auth()->user()->hasRole('admin'))
                <button
                    type="button"
                    x-data
                    @click="$dispatch('schedule:open-create')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Nueva Clase
                </button>
            @endif
        </div>
    </x-slot>

    @push('styles')
    <style>
        /* FullCalendar base overrides */
        .fc {
            --fc-border-color: rgb(229 231 235);
            --fc-button-bg-color: #3B82F6;
            --fc-button-border-color: #3B82F6;
            --fc-button-hover-bg-color: #2563EB;
            --fc-button-hover-border-color: #2563EB;
            --fc-button-active-bg-color: #1D4ED8;
            --fc-button-active-border-color: #1D4ED8;
            --fc-today-bg-color: rgba(59,130,246,0.08);
            font-family: inherit;
        }
        .fc .fc-toolbar-title {
            font-size: 1.15rem;
            font-weight: 600;
        }
        .fc .fc-button {
            font-size: 0.78rem;
            font-weight: 600;
            border-radius: 0.375rem;
            padding: 0.35rem 0.8rem;
            text-transform: none;
            letter-spacing: 0;
        }
        .fc .fc-daygrid-event {
            border-radius: 4px;
            font-size: 0.78rem;
            cursor: pointer;
        }
        .fc .fc-event-title {
            font-weight: 600;
        }
        .fc .fc-timegrid-event {
            border-radius: 5px;
        }
        .fc .fc-col-header-cell-cushion {
            font-weight: 600;
            font-size: 0.8rem;
        }
        .fc .fc-daygrid-day-number {
            font-size: 0.82rem;
        }
        /* Dark mode */
        .dark .fc {
            --fc-border-color: rgb(51 65 85);
            --fc-neutral-bg-color: rgb(30 41 59);
            --fc-page-bg-color: transparent;
            --fc-today-bg-color: rgba(59,130,246,0.12);
        }
        .dark .fc-theme-standard td,
        .dark .fc-theme-standard th,
        .dark .fc-theme-standard .fc-scrollgrid {
            border-color: rgb(51 65 85);
        }
        .dark .fc .fc-col-header-cell-cushion,
        .dark .fc .fc-daygrid-day-number,
        .dark .fc .fc-toolbar-title {
            color: rgb(226 232 240);
        }
        .dark .fc .fc-daygrid-day.fc-day-other .fc-daygrid-day-number {
            color: rgb(100 116 139);
        }
        .dark .fc .fc-timegrid-slot-label {
            color: rgb(148 163 184);
        }
        .dark .fc .fc-button-primary:not(:disabled) {
            background-color: #3B82F6;
            border-color: #3B82F6;
        }
        .dark .fc .fc-button-primary:not(:disabled):hover {
            background-color: #2563EB;
        }
        .dark .fc .fc-button-primary:disabled {
            background-color: rgb(71 85 105);
            border-color: rgb(71 85 105);
        }
        .dark .fc-theme-standard .fc-list-day-cushion {
            background-color: rgb(30 41 59);
            color: rgb(226 232 240);
        }
        .dark .fc .fc-list-event:hover td {
            background-color: rgb(51 65 85);
        }
        /* Tooltip for events */
        .fc-event:hover {
            filter: brightness(1.1);
        }
    </style>
    @endpush

    <div class="py-8" x-data="scheduleManager()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Teachers legend --}}
            @if ($teachers->isNotEmpty())
                <div class="bg-white dark:bg-graphite-900 rounded-xl border border-gray-200 dark:border-graphite-800 px-4 py-3">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                        <span class="text-xs font-semibold text-gray-500 dark:text-graphite-400 uppercase tracking-wide">Profesores:</span>
                        @foreach ($teachers as $teacher)
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $teacher['color'] }}"></span>
                                <span class="text-sm text-gray-700 dark:text-graphite-300">{{ $teacher['name'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Calendar --}}
            <div class="bg-white dark:bg-graphite-900 rounded-xl border border-gray-200 dark:border-graphite-800 p-4 sm:p-6">
                <div id="schedule-calendar"></div>
            </div>
        </div>

        {{-- ============================================================
             Modal overlay
             ============================================================ --}}
        <div
            x-show="showModal"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="showModal = false"
        >
            {{-- Backdrop --}}
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="showModal = false"
            ></div>

            {{-- Panel --}}
            <div
                x-show="showModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative bg-white dark:bg-graphite-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-graphite-700 z-10 overflow-hidden"
                style="width: 100%; max-width: 440px;"
            >
                {{-- Header color bar --}}
                <div class="h-1.5 w-full" :style="'background-color:' + (currentEvent?.color || '#3B82F6')"></div>

                <div class="p-6 space-y-5">
                    {{-- Title row --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            {{-- VIEW mode title --}}
                            <h3 x-show="modalMode === 'view'" class="text-lg font-bold text-gray-900 dark:text-graphite-100 leading-snug" x-text="currentEvent?.title"></h3>
                            {{-- CREATE / EDIT / CONFIRM-DELETE mode title --}}
                            <h3 x-show="modalMode !== 'view'" class="text-lg font-bold text-gray-900 dark:text-graphite-100">
                                <span x-show="modalMode === 'create'">Nueva Clase</span>
                                <span x-show="modalMode === 'edit'">Editar Clase</span>
                                <span x-show="modalMode === 'confirm-delete'">Eliminar clase</span>
                            </h3>
                        </div>
                        <button @click="showModal = false" class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-graphite-200 dark:hover:bg-graphite-800 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>

                    {{-- ====== VIEW MODE ====== --}}
                    <div x-show="modalMode === 'view'" class="space-y-3">
                        {{-- Teacher --}}
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full flex-shrink-0 flex items-center justify-center" :style="'background-color:' + (currentEvent?.color || '#3B82F6')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <span class="text-sm font-semibold text-gray-800 dark:text-graphite-200" x-text="currentEvent?.teacher_name"></span>
                        </div>
                        {{-- Times --}}
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span class="text-sm text-gray-700 dark:text-graphite-300" x-text="formatEventTimes(currentEvent?.start, currentEvent?.end)"></span>
                        </div>
                        {{-- Description --}}
                        <template x-if="currentEvent?.description">
                            <div class="flex items-start gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                                <p class="text-sm text-gray-700 dark:text-graphite-300" x-text="currentEvent?.description"></p>
                            </div>
                        </template>
                        {{-- Meeting link --}}
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 10l4.553-2.069A1 1 0 0 1 21 8.845v6.31a1 1 0 0 1-1.447.894L15 14M3 8a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8z"/></svg>
                            <a
                                :href="currentEvent?.meeting_link"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline break-all"
                                x-text="currentEvent?.meeting_link"
                            ></a>
                        </div>
                        {{-- Access badge --}}
                        <div class="flex items-center gap-2">
                            <template x-if="currentEvent?.is_exclusive">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Solo miembros activos
                                </span>
                            </template>
                            <template x-if="!currentEvent?.is_exclusive">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    Para todos
                                </span>
                            </template>
                        </div>
                        {{-- Actions for teacher (own) or admin --}}
                        <div x-show="canEdit()" class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-graphite-800">
                            <button
                                type="button"
                                @click="openEditModal()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md text-brand-700 bg-brand-50 hover:bg-brand-100 dark:text-brand-300 dark:bg-brand-900/30 dark:hover:bg-brand-900/50 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Editar
                            </button>
                            <button
                                type="button"
                                @click="modalMode = 'confirm-delete'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-md text-red-700 bg-red-50 hover:bg-red-100 dark:text-red-300 dark:bg-red-900/30 dark:hover:bg-red-900/50 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                Eliminar
                            </button>
                        </div>
                    </div>

                    {{-- ====== CONFIRM DELETE MODE ====== --}}
                    <div x-show="modalMode === 'confirm-delete'" class="space-y-4">
                        <div class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-100 dark:border-red-800/60">
                            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-red-100 dark:bg-red-900/50 flex items-center justify-center mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-red-600 dark:text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-red-800 dark:text-red-300">¿Eliminar esta clase?</p>
                                <p class="text-sm text-red-700 dark:text-red-400 mt-0.5 truncate" x-text="currentEvent?.title"></p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-graphite-400">Esta acción no se puede deshacer.</p>
                        <template x-if="formError">
                            <p class="text-sm text-red-600 dark:text-red-400" x-text="formError"></p>
                        </template>
                        <div class="flex items-center justify-end gap-2">
                            <button
                                type="button"
                                @click="modalMode = 'view'"
                                class="px-4 py-2 text-sm font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-graphite-300 dark:bg-graphite-800 dark:hover:bg-graphite-700 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="button"
                                @click="deleteEvent()"
                                :disabled="loading"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-red-600 hover:bg-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-show="!loading">Sí, eliminar</span>
                                <span x-show="loading">Eliminando...</span>
                            </button>
                        </div>
                    </div>

                    {{-- ====== CREATE / EDIT MODE ====== --}}
                    <form x-show="modalMode === 'create' || modalMode === 'edit'" @submit.prevent="saveEvent()" class="space-y-4">
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-200 mb-1">Título <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                x-model="form.title"
                                required
                                maxlength="200"
                                placeholder="Ej: Clase de Análisis Técnico"
                                class="w-full rounded-lg border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-800 text-gray-900 dark:text-graphite-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-400 dark:placeholder-graphite-500"
                            />
                        </div>
                        {{-- Date/time row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-200 mb-1">Inicio <span class="text-red-500">*</span></label>
                                <input
                                    type="datetime-local"
                                    x-model="form.start_time"
                                    required
                                    class="w-full rounded-lg border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-800 text-gray-900 dark:text-graphite-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-200 mb-1">Fin <span class="text-red-500">*</span></label>
                                <input
                                    type="datetime-local"
                                    x-model="form.end_time"
                                    required
                                    class="w-full rounded-lg border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-800 text-gray-900 dark:text-graphite-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                        {{-- Meeting link --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-200 mb-1">Link de reunión <span class="text-red-500">*</span></label>
                            <input
                                type="url"
                                x-model="form.meeting_link"
                                required
                                placeholder="https://meet.google.com/..."
                                class="w-full rounded-lg border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-800 text-gray-900 dark:text-graphite-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-400 dark:placeholder-graphite-500"
                            />
                        </div>
                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-200 mb-1">Descripción <span class="text-xs text-gray-400">(opcional)</span></label>
                            <textarea
                                x-model="form.description"
                                rows="3"
                                maxlength="1000"
                                placeholder="Detalles sobre la clase..."
                                class="w-full rounded-lg border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-800 text-gray-900 dark:text-graphite-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent placeholder-gray-400 dark:placeholder-graphite-500 resize-none"
                            ></textarea>
                        </div>
                        {{-- Access restriction --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-200 mb-1">Acceso</label>
                            <select
                                x-model="form.is_exclusive"
                                class="w-full rounded-lg border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-800 text-gray-900 dark:text-graphite-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            >
                                <option value="0">Para todos</option>
                                <option value="1">Solo miembros activos</option>
                            </select>
                        </div>
                        {{-- Error message --}}
                        <template x-if="formError">
                            <p class="text-sm text-red-600 dark:text-red-400" x-text="formError"></p>
                        </template>
                        {{-- Buttons --}}
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button
                                type="button"
                                @click="showModal = false"
                                class="px-4 py-2 text-sm font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-graphite-300 dark:bg-graphite-800 dark:hover:bg-graphite-700 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-white bg-brand-600 hover:bg-brand-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-show="!loading" x-text="modalMode === 'edit' ? 'Guardar cambios' : 'Crear clase'"></span>
                                <span x-show="loading">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
    (function () {
        const IS_TEACHER_OR_ADMIN = @json(auth()->user()->hasRole('teacher') || auth()->user()->hasRole('admin'));
        const IS_ADMIN            = @json(auth()->user()->hasRole('admin'));
        const CURRENT_USER_ID     = @json(auth()->id());
        const CSRF                = document.querySelector('meta[name="csrf-token"]').content;

        const ROUTES = {
            events:  '{{ route('schedules.events') }}',
            store:   '{{ route('schedules.store') }}',
            update:  '{{ url('/horarios') }}/__ID__',
            destroy: '{{ url('/horarios') }}/__ID__',
        };

        function scheduleManager() {
            return {
                showModal:    false,
                modalMode:    'view', // 'view' | 'create' | 'edit'
                currentEvent: null,
                form:         { title: '', description: '', meeting_link: '', start_time: '', end_time: '', is_exclusive: '0' },
                formError:    '',
                loading:      false,
                calendar:     null,

                init() {
                    this.$nextTick(() => this.initCalendar());

                    window.addEventListener('schedule:open-create', () => {
                        const now = new Date();
                        const pad = n => String(n).padStart(2, '0');
                        const dateStr = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}`;
                        this.openCreateModal(dateStr, '09:00', '10:00');
                    });
                },

                initCalendar() {
                    const el = document.getElementById('schedule-calendar');
                    const self = this;

                    this.calendar = new FullCalendar.Calendar(el, {
                        initialView:  'dayGridMonth',
                        locale:       'es',
                        timeZone:     'local',
                        height:       'auto',
                        firstDay:     1,
                        headerToolbar: {
                            left:   'prev,next today',
                            center: 'title',
                            right:  'dayGridMonth,timeGridWeek,timeGridDay',
                        },
                        buttonText: {
                            today:        'Hoy',
                            month:        'Mes',
                            week:         'Semana',
                            day:          'Día',
                        },
                        events(info, success, failure) {
                            fetch(`${ROUTES.events}?start=${info.startStr}&end=${info.endStr}`, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                            .then(r => r.json())
                            .then(success)
                            .catch(failure);
                        },
                        eventClick(info) {
                            self.openViewModal(info.event);
                        },
                        dateClick(info) {
                            if (!IS_TEACHER_OR_ADMIN) return;
                            self.openCreateModal(info.dateStr, '09:00', '10:00');
                        },
                        eventMouseEnter(info) {
                            info.el.style.cursor = 'pointer';
                        },
                        eventDisplay: 'block',
                        displayEventTime: true,
                        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                    });

                    this.calendar.render();
                },

                openViewModal(fcEvent) {
                    this.currentEvent = {
                        id:           fcEvent.id,
                        title:        fcEvent.title,
                        start:        fcEvent.start,
                        end:          fcEvent.end,
                        color:        fcEvent.backgroundColor,
                        teacher_id:   fcEvent.extendedProps.teacher_id,
                        teacher_name: fcEvent.extendedProps.teacher_name,
                        description:  fcEvent.extendedProps.description,
                        meeting_link: fcEvent.extendedProps.meeting_link,
                        is_exclusive: fcEvent.extendedProps.is_exclusive,
                    };
                    this.formError = '';
                    this.loading   = false;
                    this.modalMode = 'view';
                    this.showModal = true;
                },

                openCreateModal(dateStr, startTime, endTime) {
                    const startDt = dateStr.length === 10 ? `${dateStr}T${startTime}` : dateStr.slice(0, 16);
                    const endDt   = dateStr.length === 10 ? `${dateStr}T${endTime}`   : dateStr.slice(0, 16);
                    this.form      = { title: '', description: '', meeting_link: '', start_time: startDt, end_time: endDt, is_exclusive: '0' };
                    this.formError = '';
                    this.modalMode = 'create';
                    this.showModal = true;
                },

                openEditModal() {
                    const fmt = dt => {
                        if (!dt) return '';
                        const d = new Date(dt);
                        const pad = n => String(n).padStart(2, '0');
                        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
                    };
                    this.form = {
                        title:        this.currentEvent.title        || '',
                        description:  this.currentEvent.description  || '',
                        meeting_link: this.currentEvent.meeting_link || '',
                        start_time:   fmt(this.currentEvent.start),
                        end_time:     fmt(this.currentEvent.end),
                        is_exclusive: this.currentEvent.is_exclusive ? '1' : '0',
                    };
                    this.formError = '';
                    this.modalMode = 'edit';
                },

                async saveEvent() {
                    this.loading   = true;
                    this.formError = '';
                    const isEdit   = this.modalMode === 'edit';
                    const url      = isEdit
                        ? ROUTES.update.replace('__ID__', this.currentEvent.id)
                        : ROUTES.store;

                    // Convert datetime-local (browser local time) → UTC ISO string so the
                    // server always stores UTC and every timezone sees the correct local time.
                    const localToUtc = str => {
                        if (!str) return '';
                        const normalized = str.length === 16 ? str + ':00' : str;
                        return new Date(normalized).toISOString();
                    };

                    try {
                        const res = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': CSRF,
                            },
                            body: JSON.stringify({
                                title:        this.form.title,
                                description:  this.form.description,
                                meeting_link: this.form.meeting_link,
                                start_time:   localToUtc(this.form.start_time),
                                end_time:     localToUtc(this.form.end_time),
                            }),
                        });

                        const data = await res.json();

                        if (!res.ok) {
                            const first = data.errors
                                ? Object.values(data.errors).flat()[0]
                                : (data.message || 'Error al guardar');
                            this.formError = first;
                            return;
                        }

                        // Remove old event if editing
                        if (isEdit) {
                            const existing = this.calendar.getEventById(String(this.currentEvent.id));
                            if (existing) existing.remove();
                        }

                        this.calendar.addEvent(data);
                        this.showModal = false;
                    } catch {
                        this.formError = 'Error de conexión. Intenta de nuevo.';
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteEvent() {
                    this.loading = true;
                    try {
                        const res = await fetch(ROUTES.destroy.replace('__ID__', this.currentEvent.id), {
                            method: 'DELETE',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        });

                        if (!res.ok) {
                            const data = await res.json();
                            this.formError = data.message || 'Error al eliminar';
                            this.modalMode = 'confirm-delete';
                            return;
                        }

                        const existing = this.calendar.getEventById(String(this.currentEvent.id));
                        if (existing) existing.remove();
                        this.showModal = false;
                    } catch {
                        this.formError = 'Error de conexión';
                        this.modalMode = 'confirm-delete';
                    } finally {
                        this.loading = false;
                    }
                },

                canEdit() {
                    if (!this.currentEvent) return false;
                    if (IS_ADMIN) return true;
                    if (IS_TEACHER_OR_ADMIN) return Number(this.currentEvent.teacher_id) === CURRENT_USER_ID;
                    return false;
                },

                formatEventTimes(start, end) {
                    if (!start) return '';
                    const opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    const timeOpts = { hour: '2-digit', minute: '2-digit', hour12: false };
                    const locale = 'es-ES';
                    const startD = new Date(start);
                    const endD   = end ? new Date(end) : null;
                    const date   = startD.toLocaleDateString(locale, opts);
                    const t1     = startD.toLocaleTimeString(locale, timeOpts);
                    const t2     = endD ? endD.toLocaleTimeString(locale, timeOpts) : '';
                    return t2 ? `${date} · ${t1} – ${t2}` : `${date} · ${t1}`;
                },
            };
        }

        window.scheduleManager = scheduleManager;
    })();
    </script>
    @endpush
</x-app-layout>
