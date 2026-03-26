<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                    {{ __('messages.user.network.title') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">
                    {{ __('messages.user.network.description') }}
                </p>
            </div>

            <form method="GET" action="{{ route('user.network.index') }}" class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white/90 p-3 shadow-sm sm:flex-row sm:flex-wrap sm:items-center dark:border-graphite-800 dark:bg-graphite-900/90">
                <select name="depth" class="min-w-[180px] rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/30">
                    @for ($i = 1; $i <= 6; $i++)
                        <option value="{{ $i }}" @selected($depth === $i)>{{ __('messages.user.network.depth_label', ['depth' => $i]) }}</option>
                    @endfor
                </select>
                <button type="submit" class="inline-flex h-[46px] items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-xs font-semibold uppercase tracking-[0.18em] text-gray-700 shadow-sm transition hover:border-sky-400 hover:text-sky-700 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-sky-500 dark:hover:text-sky-300">
                    {{ __('messages.user.network.apply') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 lg:grid-cols-[0.8fr_1.2fr]">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-sm leading-6 text-gray-600 dark:text-graphite-300">{{ __('messages.user.network.summary') }}</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-graphite-800">
                            <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.network.kpi_direct') }}</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-graphite-100">{{ $directAffiliatesCount }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 p-4 dark:border-graphite-800">
                            <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.network.kpi_network') }}</p>
                            <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-graphite-100">{{ $networkAffiliatesCount }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                        <p class="text-xs uppercase tracking-[0.16em] text-amber-700 dark:text-amber-300">{{ __('messages.user.network.sponsor_card') }}</p>
                        @if ($sponsor)
                            <p class="mt-2 text-lg font-semibold text-amber-900 dark:text-amber-100">{{ $sponsor->name }}</p>
                            <p class="text-sm text-amber-700/80 dark:text-amber-200/80">{{ $sponsor->email }}</p>
                        @else
                            <p class="mt-2 text-sm text-amber-700 dark:text-amber-300">{{ __('messages.user.network.no_sponsor') }}</p>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.user.network.hint_zoom') }}</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.user.network.hint_drag') }}</span>
                        <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.user.network.hint_click') }}</span>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.14),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(245,158,11,0.12),_transparent_34%)]"></div>
                    <div class="absolute right-4 top-4 z-10 grid grid-cols-3 gap-2 rounded-2xl border border-gray-200/80 bg-white/85 p-2 shadow-lg backdrop-blur dark:border-graphite-700 dark:bg-graphite-900/85">
                        <span></span>
                        <button type="button" data-pan="up" class="users-tree-pan-btn">↑</button>
                        <span></span>
                        <button type="button" data-pan="left" class="users-tree-pan-btn">←</button>
                        <button type="button" data-pan="center" class="users-tree-pan-btn">⌂</button>
                        <button type="button" data-pan="right" class="users-tree-pan-btn">→</button>
                        <span></span>
                        <button type="button" data-pan="down" class="users-tree-pan-btn">↓</button>
                        <span></span>
                    </div>
                    <div id="user-network-graph" class="users-tree-surface h-[72vh] w-full rounded-2xl"></div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="user-network-insights-modal" :show="false" maxWidth="2xl">
        <div class="p-6">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 id="user-network-modal-name" class="text-lg font-semibold text-gray-900 dark:text-graphite-100"></h3>
                    <p id="user-network-modal-meta" class="mt-1 text-sm text-gray-500 dark:text-graphite-400"></p>
                </div>
                <span id="user-network-modal-relation" class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-300"></span>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-4">
                <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-800">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.network.modal.membership') }}</p>
                    <p id="user-network-modal-membership" class="text-sm font-medium text-gray-900 dark:text-graphite-100">-</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-800">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.network.modal.balance') }}</p>
                    <p id="user-network-modal-balance" class="text-sm font-medium text-gray-900 dark:text-graphite-100">$0.00</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-800">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.network.modal.pending') }}</p>
                    <p id="user-network-modal-pending" class="text-sm font-medium text-amber-600">$0.00</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-800">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.network.modal.last_payment') }}</p>
                    <p id="user-network-modal-last-payment" class="text-sm font-medium text-gray-900 dark:text-graphite-100">-</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.user.network.modal.sponsors') }}</h4>
                    <ul id="user-network-modal-sponsors" class="mt-2 space-y-1 text-sm text-gray-700 dark:text-graphite-200"></ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.user.network.modal.affiliates') }}</h4>
                    <div id="user-network-modal-affiliates" class="mt-2 space-y-2 text-sm text-gray-700 dark:text-graphite-200"></div>
                </div>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script src="https://unpkg.com/vis-network@9.1.9/dist/vis-network.min.js"></script>
        <script>
            (() => {
                const graph = @json($graph);
                const viewerId = Number(graph.viewer_id);
                const sponsorId = graph.sponsor_id ? Number(graph.sponsor_id) : null;
                const insightsPattern = @json(route('user.network.insights', ['user' => '__ID__']));

                const paletteByRole = {
                    viewer: ['#FDE68A', '#D97706', '#FFF7ED'],
                    sponsor: ['#BFDBFE', '#2563EB', '#EFF6FF'],
                    affiliate: ['#A7F3D0', '#059669', '#ECFDF5'],
                };

                const resolveRole = (nodeId) => {
                    if (nodeId === viewerId) {
                        return 'viewer';
                    }

                    if (sponsorId !== null && nodeId === sponsorId) {
                        return 'sponsor';
                    }

                    return 'affiliate';
                };

                const buildAvatarSvg = (node) => {
                    const role = resolveRole(Number(node.id));
                    const [ring, primary, bg] = paletteByRole[role] || paletteByRole.affiliate;

                    return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
                        <svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140">
                            <defs>
                                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                    <feDropShadow dx="0" dy="8" stdDeviation="8" flood-color="#0f172a" flood-opacity="0.14"/>
                                </filter>
                                <linearGradient id="panel" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#ffffff"/>
                                    <stop offset="100%" stop-color="${bg}"/>
                                </linearGradient>
                            </defs>
                            <circle cx="70" cy="70" r="56" fill="url(#panel)" stroke="${ring}" stroke-width="6" filter="url(#shadow)"/>
                            <circle cx="70" cy="70" r="46" fill="#ffffff" opacity="0.92"/>
                            <circle cx="70" cy="53" r="13" fill="${primary}" opacity="0.92"/>
                            <path d="M46 96c6-13 15-20 24-20s18 7 24 20" fill="${primary}" opacity="0.92"/>
                        </svg>
                    `)}`;
                };

                const container = document.getElementById('user-network-graph');
                const nodes = new vis.DataSet(
                    graph.nodes.map((node) => ({
                        id: node.id,
                        label: `${node.label}`,
                        title: `${node.email || ''}`,
                        shape: 'circularImage',
                        image: buildAvatarSvg(node),
                        brokenImage: buildAvatarSvg(node),
                        size: Number(node.id) === viewerId ? 34 : 30,
                        font: {
                            face: 'Figtree, Arial, sans-serif',
                            size: 11,
                            color: '#1f2937',
                            strokeWidth: 0,
                            background: 'rgba(255,253,248,0.88)',
                            vadjust: 0,
                        },
                    }))
                );

                const edges = new vis.DataSet(graph.edges.map((edge) => ({
                    from: edge.from,
                    to: edge.to,
                    arrows: 'to',
                    color: { color: '#94a3b8', highlight: '#475569' },
                    width: 2,
                    arrowStrikethrough: false,
                    smooth: { type: 'cubicBezier', roundness: 0.4 },
                })));

                const network = new vis.Network(container, { nodes, edges }, {
                    autoResize: true,
                    interaction: {
                        dragNodes: false,
                        dragView: true,
                        zoomView: true,
                        hover: true,
                    },
                    physics: { enabled: false },
                    layout: {
                        hierarchical: {
                            enabled: true,
                            direction: 'UD',
                            sortMethod: 'directed',
                            levelSeparation: 210,
                            nodeSpacing: 170,
                            treeSpacing: 220,
                            blockShifting: true,
                            edgeMinimization: true,
                            parentCentralization: true,
                        },
                    },
                });

                network.on('doubleClick', () => {
                    network.fit({ animation: { duration: 350 } });
                });

                document.querySelectorAll('[data-pan]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const direction = button.getAttribute('data-pan');
                        if (direction === 'center') {
                            network.fit({ animation: { duration: 300 } });
                            return;
                        }

                        const current = network.getViewPosition();
                        const panStep = 140;

                        network.moveTo({
                            position: {
                                x: current.x + (direction === 'left' ? -panStep : direction === 'right' ? panStep : 0),
                                y: current.y + (direction === 'up' ? -panStep : direction === 'down' ? panStep : 0),
                            },
                            animation: { duration: 220, easingFunction: 'easeInOutQuad' },
                        });
                    });
                });

                const relationLabel = {
                    self: @json(__('messages.user.network.modal.relations.self')),
                    sponsor: @json(__('messages.user.network.modal.relations.sponsor')),
                    affiliate: @json(__('messages.user.network.modal.relations.affiliate')),
                };

                network.on('click', async (params) => {
                    if (!params.nodes || params.nodes.length === 0) {
                        return;
                    }

                    const userId = params.nodes[0];
                    const endpoint = insightsPattern.replace('__ID__', String(userId));

                    try {
                        const response = await fetch(endpoint, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        const data = payload.data;

                        document.getElementById('user-network-modal-name').textContent = data.user.name || '-';
                        document.getElementById('user-network-modal-meta').textContent = `${data.user.email || '-'} • #${data.user.id}`;
                        document.getElementById('user-network-modal-relation').textContent = relationLabel[data.scope.relation] || data.scope.relation;
                        document.getElementById('user-network-modal-membership').textContent = data.user.membership || '-';
                        document.getElementById('user-network-modal-balance').textContent = `$${Number(data.user.commission_balance || 0).toFixed(2)}`;
                        document.getElementById('user-network-modal-pending').textContent = `$${Number(data.user.pending_profits_total || 0).toFixed(2)}`;
                        document.getElementById('user-network-modal-last-payment').textContent = data.user.last_approved_payment_at || '-';

                        const sponsorsEl = document.getElementById('user-network-modal-sponsors');
                        sponsorsEl.innerHTML = '';

                        if ((data.sponsors || []).length === 0) {
                            sponsorsEl.innerHTML = `<li>{{ __('messages.user.network.modal.no_sponsors') }}</li>`;
                        } else {
                            data.sponsors.forEach((item) => {
                                const li = document.createElement('li');
                                li.textContent = `N${item.level}: ${item.name} (#${item.id})`;
                                sponsorsEl.appendChild(li);
                            });
                        }

                        const affiliatesEl = document.getElementById('user-network-modal-affiliates');
                        affiliatesEl.innerHTML = '';
                        const levels = Object.keys(data.affiliates || {});

                        if (levels.length === 0 || data.scope.relation === 'sponsor') {
                            affiliatesEl.innerHTML = `<p>{{ __('messages.user.network.modal.no_affiliates_visible') }}</p>`;
                        } else {
                            levels.forEach((key) => {
                                const wrapper = document.createElement('div');
                                const title = document.createElement('p');
                                const people = data.affiliates[key] || [];
                                title.className = 'font-medium';
                                title.textContent = `Nivel ${key.replace('level_', '')} (${people.length})`;
                                wrapper.appendChild(title);

                                const list = document.createElement('p');
                                list.className = 'text-gray-600 dark:text-graphite-400';
                                list.textContent = people.length === 0
                                    ? '-'
                                    : people.map((person) => `${person.name} (#${person.id})`).join(', ');

                                wrapper.appendChild(list);
                                affiliatesEl.appendChild(wrapper);
                            });
                        }

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'user-network-insights-modal' }));
                    } catch (error) {
                        console.error(error);
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
