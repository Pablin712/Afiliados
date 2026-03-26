<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                {{ __('messages.admin.users_tree.title') }}
            </h2>
            <form method="GET" action="{{ route('admin.users-tree.index') }}" class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white/90 p-3 shadow-sm sm:flex-row sm:flex-wrap sm:items-center dark:border-graphite-800 dark:bg-graphite-900/90">
                <select name="root_user_id" class="min-w-[260px] rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm transition focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-200 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 dark:focus:border-amber-500 dark:focus:ring-amber-900/30">
                    <option value="">{{ __('messages.admin.users_tree.root_default') }}</option>
                    @foreach ($rootOptions as $option)
                        <option value="{{ $option->id }}" @selected((string) $rootUserId === (string) $option->id)>
                            #{{ $option->id }} - {{ $option->name }}
                        </option>
                    @endforeach
                </select>
                <select name="depth" class="min-w-[150px] rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm transition focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-200 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 dark:focus:border-sky-500 dark:focus:ring-sky-900/30">
                    @for ($i = 2; $i <= 10; $i++)
                        <option value="{{ $i }}" @selected($depth === $i)>{{ __('messages.admin.users_tree.depth_label', ['depth' => $i]) }}</option>
                    @endfor
                </select>
                <button type="submit" class="inline-flex h-[46px] items-center justify-center rounded-xl border border-gray-300 bg-white px-5 text-xs font-semibold uppercase tracking-[0.18em] text-gray-700 shadow-sm transition hover:border-amber-400 hover:text-amber-700 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-amber-500 dark:hover:text-amber-300">
                    {{ __('messages.admin.users_tree.apply') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                <p class="text-sm leading-6 text-gray-600 dark:text-graphite-300">{{ __('messages.admin.users_tree.description') }}</p>
                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.admin.users_tree.hint_zoom') }}</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.admin.users_tree.hint_drag') }}</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.admin.users_tree.hint_click') }}</span>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.12),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.12),_transparent_32%)]"></div>
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
                <div id="users-tree-network" class="users-tree-surface h-[72vh] w-full rounded-2xl"></div>
            </div>
        </div>
    </div>

    <x-modal name="user-tree-insights-modal" :show="false" maxWidth="2xl">
        <div class="p-6">
            <h3 id="tree-modal-user-name" class="text-lg font-semibold text-gray-900 dark:text-graphite-100"></h3>
            <p id="tree-modal-user-meta" class="mt-1 text-sm text-gray-500 dark:text-graphite-400"></p>

            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-800">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.users_tree.modal.membership') }}</p>
                    <p id="tree-modal-membership" class="text-sm font-medium text-gray-900 dark:text-graphite-100">-</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-800">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.users_tree.modal.balance') }}</p>
                    <p id="tree-modal-balance" class="text-sm font-medium text-gray-900 dark:text-graphite-100">$0.00</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-800">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.users_tree.modal.last_payment') }}</p>
                    <p id="tree-modal-last-payment" class="text-sm font-medium text-gray-900 dark:text-graphite-100">-</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.users_tree.modal.sponsors') }}</h4>
                    <ul id="tree-modal-sponsors" class="mt-2 space-y-1 text-sm text-gray-700 dark:text-graphite-200"></ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.users_tree.modal.affiliates') }}</h4>
                    <div id="tree-modal-affiliates" class="mt-2 space-y-2 text-sm text-gray-700 dark:text-graphite-200"></div>
                </div>
            </div>
        </div>
    </x-modal>

    @push('scripts')
        <script src="https://unpkg.com/vis-network@9.1.9/dist/vis-network.min.js"></script>
        <script>
            (() => {
                const graph = @json($graph);
                const usersTreeInsightsPattern = @json(route('admin.users-tree.insights', ['user' => '__ID__']));
                const inboundNodeIds = new Set(graph.edges.map((edge) => edge.to));

                const paletteByMembership = {
                    free: ['#CBD5E1', '#64748B', '#F8FAFC'],
                    customer: ['#BFDBFE', '#2563EB', '#EFF6FF'],
                    beginner: ['#FDE68A', '#D97706', '#FFFBEB'],
                    explorer: ['#A7F3D0', '#059669', '#ECFDF5'],
                    professional: ['#DDD6FE', '#7C3AED', '#F5F3FF'],
                    elite: ['#FBCFE8', '#DB2777', '#FFF1F2'],
                    root: ['#FDE68A', '#B45309', '#FFF7ED'],
                };

                const buildInitials = (label) => String(label || 'U')
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2)
                    .map((part) => part[0]?.toUpperCase() || '')
                    .join('') || 'U';

                const buildAvatarSvg = (node) => {
                    const isRoot = !inboundNodeIds.has(node.id);
                    const membershipKey = String(node.membership || '').toLowerCase();
                    const [ring, primary, bg] = paletteByMembership[isRoot ? 'root' : membershipKey] || ['#D6D3D1', '#44403C', '#FAFAF9'];
                    const badge = isRoot ? '#f59e0b' : primary;

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
                            <circle cx="102" cy="38" r="9" fill="${badge}" stroke="#ffffff" stroke-width="3"/>
                        </svg>
                    `)}`;
                };

                const container = document.getElementById('users-tree-network');
                const nodes = new vis.DataSet(
                    graph.nodes.map((node) => ({
                        id: node.id,
                        label: `${node.label}`,
                        title: `${node.email || ''}`,
                        shape: 'circularImage',
                        image: buildAvatarSvg(node),
                        brokenImage: buildAvatarSvg(node),
                        size: 30,
                        font: {
                            face: 'Figtree, Arial, sans-serif',
                            size: 11,
                            color: '#1f2937',
                            strokeWidth: 0,
                            strokeColor: '#fffdf8',
                            vadjust: 0,
                            background: 'rgba(255,253,248,0.88)',
                        },
                        shadow: {
                            enabled: true,
                            color: 'rgba(15, 23, 42, 0.18)',
                            size: 10,
                            x: 0,
                            y: 6,
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
                    physics: {
                        enabled: false,
                        stabilization: { iterations: 250 },
                        barnesHut: {
                            springLength: 130,
                            springConstant: 0.02,
                            damping: 0.4,
                        },
                    },
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

                const panButtons = document.querySelectorAll('[data-pan]');
                const panStep = 140;

                panButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const direction = button.getAttribute('data-pan');
                        if (direction === 'center') {
                            network.fit({ animation: { duration: 300 } });
                            return;
                        }

                        const current = network.getViewPosition();
                        let nextX = current.x;
                        let nextY = current.y;

                        if (direction === 'up') nextY -= panStep;
                        if (direction === 'down') nextY += panStep;
                        if (direction === 'left') nextX -= panStep;
                        if (direction === 'right') nextX += panStep;

                        network.moveTo({
                            position: { x: nextX, y: nextY },
                            animation: { duration: 220, easingFunction: 'easeInOutQuad' },
                        });
                    });
                });

                network.on('click', async (params) => {
                    if (!params.nodes || params.nodes.length === 0) {
                        return;
                    }

                    const userId = params.nodes[0];
                    const endpoint = usersTreeInsightsPattern.replace('__ID__', String(userId));

                    try {
                        const response = await fetch(endpoint, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const payload = await response.json();
                        const data = payload.data;

                        document.getElementById('tree-modal-user-name').textContent = data.user.name || '-';
                        document.getElementById('tree-modal-user-meta').textContent = `${data.user.email || '-'} • #${data.user.id}`;
                        document.getElementById('tree-modal-membership').textContent = data.user.membership || '-';
                        document.getElementById('tree-modal-balance').textContent = `$${Number(data.user.commission_balance || 0).toFixed(2)}`;
                        document.getElementById('tree-modal-last-payment').textContent = data.user.last_approved_payment_at || '-';

                        const sponsorsEl = document.getElementById('tree-modal-sponsors');
                        sponsorsEl.innerHTML = '';
                        if ((data.sponsors || []).length === 0) {
                            sponsorsEl.innerHTML = `<li>{{ __('messages.admin.users_tree.modal.no_sponsors') }}</li>`;
                        } else {
                            data.sponsors.forEach((item) => {
                                const li = document.createElement('li');
                                li.textContent = `N${item.level}: ${item.name} (#${item.id})`;
                                sponsorsEl.appendChild(li);
                            });
                        }

                        const affiliatesEl = document.getElementById('tree-modal-affiliates');
                        affiliatesEl.innerHTML = '';
                        const levels = Object.keys(data.affiliates || {});

                        if (levels.length === 0) {
                            affiliatesEl.innerHTML = `<p>{{ __('messages.admin.users_tree.modal.no_affiliates') }}</p>`;
                        } else {
                            levels.forEach((key) => {
                                const wrapper = document.createElement('div');
                                const levelTitle = document.createElement('p');
                                const levelNum = key.replace('level_', '');
                                const people = data.affiliates[key] || [];
                                levelTitle.className = 'font-medium';
                                levelTitle.textContent = `Nivel ${levelNum} (${people.length})`;
                                wrapper.appendChild(levelTitle);

                                const list = document.createElement('p');
                                list.className = 'text-gray-600 dark:text-graphite-400';
                                list.textContent = people.length === 0
                                    ? '-'
                                    : people.map((person) => `${person.name} (#${person.id})`).join(', ');

                                wrapper.appendChild(list);
                                affiliatesEl.appendChild(wrapper);
                            });
                        }

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'user-tree-insights-modal' }));
                    } catch (error) {
                        console.error(error);
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
