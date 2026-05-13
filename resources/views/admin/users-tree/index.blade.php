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

                {{-- Mode toggle: top-left --}}
                <div class="absolute left-4 top-4 z-10 flex gap-1 rounded-xl border border-gray-200/80 bg-white/85 p-1 shadow-lg backdrop-blur dark:border-graphite-700 dark:bg-graphite-900/85">
                    <button type="button" id="btn-mode-network"
                            class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition-all bg-white dark:bg-graphite-800 text-gray-900 dark:text-graphite-100 shadow-sm">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="shrink-0">
                            <circle cx="7" cy="7" r="2" fill="currentColor"/>
                            <circle cx="2" cy="2" r="1.5" fill="currentColor"/>
                            <circle cx="12" cy="2" r="1.5" fill="currentColor"/>
                            <circle cx="2" cy="12" r="1.5" fill="currentColor"/>
                            <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                            <line x1="7" y1="7" x2="2" y2="2" stroke="currentColor" stroke-width="1"/>
                            <line x1="7" y1="7" x2="12" y2="2" stroke="currentColor" stroke-width="1"/>
                            <line x1="7" y1="7" x2="2" y2="12" stroke="currentColor" stroke-width="1"/>
                            <line x1="7" y1="7" x2="12" y2="12" stroke="currentColor" stroke-width="1"/>
                        </svg>
                        Red
                    </button>
                    <button type="button" id="btn-mode-hierarchical"
                            class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition-all text-gray-500 dark:text-graphite-400 hover:text-gray-700 dark:hover:text-graphite-200">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" class="shrink-0">
                            <circle cx="7" cy="1.5" r="1.5" fill="currentColor"/>
                            <circle cx="3" cy="6.5" r="1.5" fill="currentColor"/>
                            <circle cx="11" cy="6.5" r="1.5" fill="currentColor"/>
                            <circle cx="1.5" cy="12" r="1.5" fill="currentColor"/>
                            <circle cx="4.5" cy="12" r="1.5" fill="currentColor"/>
                            <circle cx="9.5" cy="12" r="1.5" fill="currentColor"/>
                            <circle cx="12.5" cy="12" r="1.5" fill="currentColor"/>
                            <line x1="7" y1="3" x2="3" y2="5" stroke="currentColor" stroke-width="1"/>
                            <line x1="7" y1="3" x2="11" y2="5" stroke="currentColor" stroke-width="1"/>
                            <line x1="3" y1="8" x2="1.5" y2="10.5" stroke="currentColor" stroke-width="1"/>
                            <line x1="3" y1="8" x2="4.5" y2="10.5" stroke="currentColor" stroke-width="1"/>
                            <line x1="11" y1="8" x2="9.5" y2="10.5" stroke="currentColor" stroke-width="1"/>
                            <line x1="11" y1="8" x2="12.5" y2="10.5" stroke="currentColor" stroke-width="1"/>
                        </svg>
                        Arbol
                    </button>
                </div>

                {{-- Pan controls: top-right --}}
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
                const inboundNodeIds = new Set(graph.edges.map((e) => e.to));

                const paletteByMembership = Object.assign(Object.create(null), {
                    free:         ['#FECACA', '#991B1B', '#FEE2E2'],
                    customer:     ['#A7F3D0', '#059669', '#ECFDF5'],
                    beginner:     ['#A7F3D0', '#059669', '#ECFDF5'],
                    constructor:  ['#A7F3D0', '#059669', '#ECFDF5'],
                    explorer:     ['#A7F3D0', '#059669', '#ECFDF5'],
                    professional: ['#A7F3D0', '#059669', '#ECFDF5'],
                    elite:        ['#A7F3D0', '#059669', '#ECFDF5'],
                    master:       ['#A7F3D0', '#059669', '#ECFDF5'],
                    legend:       ['#A7F3D0', '#059669', '#ECFDF5'],
                    root:         ['#93C5FD', '#1D4ED8', '#EFF6FF'],
                });

                const buildAvatarSvg = (node) => {
                    const isRoot = !inboundNodeIds.has(node.id);
                    const key = String(node.membership || '').toLowerCase();
                    const [ring, primary, bg] = paletteByMembership[isRoot ? 'root' : key] || ['#D6D3D1', '#44403C', '#FAFAF9'];
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

                // Shared nodes DataSet (same for both modes)
                const nodeDegree = graph.edges.reduce((acc, e) => {
                    acc[e.from] = (acc[e.from] || 0) + 1;
                    acc[e.to]   = (acc[e.to]   || 0) + 1;
                    return acc;
                }, {});

                const nodes = new vis.DataSet(
                    graph.nodes.map((node) => ({
                        id:    node.id,
                        label: `${node.label}`,
                        title: `${node.email || ''}`,
                        shape: 'circularImage',
                        image: buildAvatarSvg(node),
                        brokenImage: buildAvatarSvg(node),
                        size:  42,
                        mass:  Math.max(1, Math.min(12, (nodeDegree[node.id] || 0) * 0.8 + 1)),
                        margin: 32,
                        font: {
                            face: 'Figtree, Arial, sans-serif',
                            size: 12,
                            color: '#0f172a',
                            strokeWidth: 0,
                            strokeColor: '#ffffff',
                            vadjust: 24,
                            multi: 'html',
                            align: 'bottom',
                            background: 'rgba(255,255,255,0.92)',
                        },
                        shadow: { enabled: true, color: 'rgba(15, 23, 42, 0.15)', size: 15, x: 0, y: 8 },
                    }))
                );

                // Branch-color palette for hierarchical mode
                const branchPalette = ['#c026d3', '#0d9488', '#ea580c', '#2563eb', '#16a34a', '#dc2626', '#7c3aed', '#0891b2', '#d97706', '#0284c7'];

                function computeBranchColors() {
                    const rootId = graph.nodes.find((n) => !inboundNodeIds.has(n.id))?.id;
                    const nodeColor = {};
                    const queue = [];

                    graph.edges.filter((e) => e.from === rootId).forEach((e, idx) => {
                        nodeColor[e.to] = branchPalette[idx % branchPalette.length];
                        queue.push(e.to);
                    });

                    const visited = new Set(queue);
                    while (queue.length) {
                        const id = queue.shift();
                        const color = nodeColor[id];
                        graph.edges.filter((e) => e.from === id).forEach((e) => {
                            if (!visited.has(e.to)) {
                                visited.add(e.to);
                                nodeColor[e.to] = color;
                                queue.push(e.to);
                            }
                        });
                    }
                    return nodeColor;
                }

                function buildNetworkEdges() {
                    return graph.edges.map((e) => ({
                        from: e.from,
                        to:   e.to,
                        arrows: 'to',
                        color: { color: '#94a3b8', highlight: '#475569' },
                        width: 2,
                        length: 280,
                        arrowStrikethrough: false,
                        smooth: { type: 'dynamic', roundness: 0.3 },
                    }));
                }

                function buildHierarchicalEdges() {
                    const nodeColor = computeBranchColors();
                    return graph.edges.map((e) => {
                        const color = nodeColor[e.to] || '#94a3b8';
                        return {
                            from: e.from,
                            to:   e.to,
                            color: { color, highlight: color, hover: color },
                            width: 3.5,
                            smooth: { type: 'cubicBezier', forceDirection: 'vertical', roundness: 0.5 },
                        };
                    });
                }

                const networkOptions = {
                    autoResize: true,
                    interaction: { dragNodes: true, dragView: true, zoomView: true, hover: true },
                    physics: {
                        enabled: true,
                        solver: 'repulsion',
                        repulsion: {
                            centralGravity: 0.01,
                            springLength: 420,
                            springConstant: 0.04,
                            nodeDistance: 320,
                            damping: 0.92,
                        },
                        stabilization: { enabled: true, iterations: 800, fit: true },
                        minVelocity: 1,
                        maxVelocity: 60,
                    },
                    layout: { improvedLayout: true },
                    edges: {
                        smooth: { enabled: true, type: 'dynamic' },
                        color: { color: '#64748B', highlight: '#1E293B', hover: '#1E293B' },
                    },
                };

                const hierarchicalOptions = {
                    autoResize: true,
                    interaction: { dragNodes: false, dragView: true, zoomView: true, hover: true },
                    physics: { enabled: false },
                    layout: {
                        hierarchical: {
                            enabled: true,
                            direction: 'UD',
                            sortMethod: 'directed',
                            levelSeparation: 130,
                            nodeSpacing: 90,
                            treeSpacing: 200,
                            blockShifting: true,
                            edgeMinimization: true,
                            parentCentralization: true,
                        },
                    },
                    edges: {
                        smooth: { enabled: true, type: 'cubicBezier', forceDirection: 'vertical', roundness: 0.5 },
                    },
                };

                // State
                const container = document.getElementById('users-tree-network');
                let currentNetwork = null;
                let currentMode = 'network';

                async function handleNodeClick(params) {
                    if (!params.nodes || params.nodes.length === 0) return;
                    const userId = params.nodes[0];
                    const endpoint = usersTreeInsightsPattern.replace('__ID__', String(userId));

                    try {
                        const response = await fetch(endpoint, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (!response.ok) return;

                        const payload = await response.json();
                        const data = payload.data;

                        document.getElementById('tree-modal-user-name').textContent    = data.user.name || '-';
                        document.getElementById('tree-modal-user-meta').textContent    = `${data.user.email || '-'} • #${data.user.id}`;
                        document.getElementById('tree-modal-membership').textContent   = data.user.membership || '-';
                        document.getElementById('tree-modal-balance').textContent      = `$${Number(data.user.commission_balance || 0).toFixed(2)}`;
                        document.getElementById('tree-modal-last-payment').textContent = data.user.last_approved_payment_at || '-';

                        const sponsorsEl = document.getElementById('tree-modal-sponsors');
                        sponsorsEl.innerHTML = '';
                        if (!(data.sponsors || []).length) {
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
                        if (!levels.length) {
                            affiliatesEl.innerHTML = `<p>{{ __('messages.admin.users_tree.modal.no_affiliates') }}</p>`;
                        } else {
                            levels.forEach((key) => {
                                const wrapper = document.createElement('div');
                                const levelTitle = document.createElement('p');
                                const people = data.affiliates[key] || [];
                                levelTitle.className = 'font-medium';
                                levelTitle.textContent = `Nivel ${key.replace('level_', '')} (${people.length})`;
                                wrapper.appendChild(levelTitle);

                                const list = document.createElement('p');
                                list.className = 'text-gray-600 dark:text-graphite-400';
                                list.textContent = people.length
                                    ? people.map((p) => `${p.name} (#${p.id})`).join(', ')
                                    : '-';
                                wrapper.appendChild(list);
                                affiliatesEl.appendChild(wrapper);
                            });
                        }

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'user-tree-insights-modal' }));
                    } catch (err) {
                        console.error(err);
                    }
                }

                function createNetwork(mode) {
                    if (currentNetwork) {
                        currentNetwork.destroy();
                        currentNetwork = null;
                    }

                    const edgeArray = mode === 'hierarchical' ? buildHierarchicalEdges() : buildNetworkEdges();
                    const edges = new vis.DataSet(edgeArray);
                    const options = mode === 'hierarchical' ? hierarchicalOptions : networkOptions;

                    currentNetwork = new vis.Network(container, { nodes, edges }, options);
                    currentNetwork.on('doubleClick', () => currentNetwork.fit({ animation: { duration: 350 } }));
                    currentNetwork.on('click', handleNodeClick);

                    if (mode === 'hierarchical') {
                        currentNetwork.once('stabilized', () => {
                            currentNetwork.fit({ animation: { duration: 600 } });
                        });
                    }
                }

                // Pan buttons — set up once, reference currentNetwork via closure
                const panStep = 140;
                document.querySelectorAll('[data-pan]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        if (!currentNetwork) return;
                        const dir = btn.getAttribute('data-pan');
                        if (dir === 'center') {
                            currentNetwork.fit({ animation: { duration: 300 } });
                            return;
                        }
                        const pos = currentNetwork.getViewPosition();
                        currentNetwork.moveTo({
                            position: {
                                x: pos.x + (dir === 'right' ? panStep : dir === 'left' ? -panStep : 0),
                                y: pos.y + (dir === 'down'  ? panStep : dir === 'up'   ? -panStep : 0),
                            },
                            animation: { duration: 220, easingFunction: 'easeInOutQuad' },
                        });
                    });
                });

                // Mode toggle
                const btnNetwork      = document.getElementById('btn-mode-network');
                const btnHierarchical = document.getElementById('btn-mode-hierarchical');
                const ACTIVE   = ['bg-white', 'dark:bg-graphite-800', 'text-gray-900', 'dark:text-graphite-100', 'shadow-sm'];
                const INACTIVE = ['text-gray-500', 'dark:text-graphite-400', 'hover:text-gray-700', 'dark:hover:text-graphite-200'];

                function setModeUI(mode) {
                    if (mode === 'network') {
                        btnNetwork.classList.add(...ACTIVE);
                        btnNetwork.classList.remove(...INACTIVE);
                        btnHierarchical.classList.remove(...ACTIVE);
                        btnHierarchical.classList.add(...INACTIVE);
                    } else {
                        btnHierarchical.classList.add(...ACTIVE);
                        btnHierarchical.classList.remove(...INACTIVE);
                        btnNetwork.classList.remove(...ACTIVE);
                        btnNetwork.classList.add(...INACTIVE);
                    }
                }

                btnNetwork.addEventListener('click', () => {
                    if (currentMode === 'network') return;
                    currentMode = 'network';
                    setModeUI('network');
                    createNetwork('network');
                });

                btnHierarchical.addEventListener('click', () => {
                    if (currentMode === 'hierarchical') return;
                    currentMode = 'hierarchical';
                    setModeUI('hierarchical');
                    createNetwork('hierarchical');
                });

                // Initial render
                createNetwork('network');
            })();
        </script>
    @endpush
</x-app-layout>
