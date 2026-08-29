@php
    $user = auth()->user();
    $parts = explode(' ', trim($user->name ?? '?'));
    $initials = strtoupper(Str::substr($parts[0], 0, 1) . (isset($parts[1]) ? Str::substr($parts[1], 0, 1) : ''));
    $role = ($user->is_admin ?? false) ? 'Administrador' : 'Operador';

    $nav = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Produtos', 'icon' => 'box', 'route' => 'produtos.index', 'active' => request()->routeIs('produtos.*')],
        ['label' => 'Entrada de Estoque', 'icon' => 'arrow-up', 'route' => 'entrada', 'active' => request()->routeIs('entrada')],
        ['label' => 'Saída de Estoque', 'icon' => 'arrow-down', 'route' => 'saida', 'active' => request()->routeIs('saida')],
        ['label' => 'Movimentações', 'icon' => 'list', 'route' => 'movimentacoes', 'active' => request()->routeIs('movimentacoes')],
        ['label' => 'Inventário', 'icon' => 'clipboard', 'route' => 'inventario', 'active' => request()->routeIs('inventario')],
        ['label' => 'Relatórios', 'icon' => 'chart', 'route' => 'relatorios', 'active' => request()->routeIs('relatorios')],
    ];

    $configActive = request()->routeIs('configuracoes') || request()->routeIs('categorias.*') || request()->routeIs('centros-custo.*') || request()->routeIs('usuarios') || request()->routeIs('funcionarios.*') || request()->routeIs('unidades.*') || request()->routeIs('etiquetas.*');
    $configOpen = $configActive;

    $titles = [
        'dashboard' => 'Dashboard',
        'produtos.index' => 'Produtos',
        'entrada' => 'Entrada de Estoque',
        'saida' => 'Saída de Estoque',
        'movimentacoes' => 'Movimentações',
        'centros-custo.index' => 'Centros de Custo',
        'inventario' => 'Inventário',
        'relatorios' => 'Relatórios',
        'usuarios' => 'Usuários',
        'configuracoes' => 'Configurações',
        'etiquetas.index' => 'Etiquetas',
    ];
    $pageTitle = $title ?? ($titles[request()->route()?->getName() ?? 'dashboard'] ?? 'Estoque');
@endphp
<!doctype html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} · Estoque</title>
    <link href="{{ asset('vendor/bladewind/css/bladewind-ui.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('vendor/bladewind/css/animate.min.css') }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-mv-bg text-mv-text antialiased">
    <div class="flex h-screen overflow-hidden">

        {{-- Overlay mobile --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-black/60 md:hidden"></div>

        {{-- Sidebar --}}
        <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-[220px] -translate-x-full flex-col border-r border-mv-border bg-mv-sidebar transition-transform md:static md:translate-x-0">
            {{-- Logo --}}
            <div class="sidebar-logo flex h-16 flex-shrink-0 items-center border-b border-mv-border bg-mv-accent px-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded bg-white">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" fill="#c8102e"/></svg>
                    </div>
                    <div class="sidebar-logo-text">
                        <p class="m-0 text-xs font-bold tracking-wider text-white leading-tight">Meu Estoque</p>
                        <p class="m-0 text-[10px] tracking-wide text-red-200 leading-tight">São Mateus — Estoque</p>
                    </div>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto p-2">
                <button id="sidebar-collapse-toggle" type="button" title="Recolher menu" class="sidebar-collapse-toggle mb-1 flex w-full items-center gap-2.5 rounded-md px-2.5 py-[7px] text-[14px] text-mv-text-secondary transition-colors hover:bg-white/5 hover:text-mv-text">
                    <x-app.icon name="panel-left" :size="15" /> <span class="nav-label">Recolher menu</span>
                </button>
                <div class="mx-1 mb-1.5 border-b border-mv-border"></div>
                @foreach ($nav as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'mb-0.5 flex w-full items-center gap-2.5 rounded-md px-2.5 py-[7px] text-[14px] transition-colors',
                            'bg-mv-accent-soft text-mv-accent-hover font-medium' => $item['active'],
                            'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text font-normal' => ! $item['active'],
                        ])
                    >
                        <x-app.icon :name="$item['icon']" :size="16" />
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                {{-- Configurações dropdown --}}
                <div class="mb-0.5">
                    <button type="button" id="config-toggle" data-config-toggle
                        @class([
                            'flex w-full items-center gap-2.5 rounded-md px-2.5 py-[7px] text-[14px] transition-colors',
                            'bg-mv-accent-soft text-mv-accent-hover font-medium' => $configActive && !$configOpen,
                            'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text font-normal' => !$configActive,
                        ])
                    >
                        <x-app.icon name="settings" :size="16" />
                        <span class="nav-label flex-1 text-left">Configurações</span>
                        <x-app.icon name="chevron-down" :size="12" class="nav-label transition-transform {{ $configOpen ? 'rotate-180' : '' }}" />
                    </button>
                    <div id="config-submenu" class="{{ $configOpen ? '' : 'hidden' }} ml-4 mt-0.5 space-y-0.5 border-l border-mv-border pl-3">
                        <a href="{{ route('categorias.index') }}"
                            @class([
                                'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[14px] transition-colors',
                                'text-mv-accent font-medium bg-mv-accent-soft' => request()->routeIs('categorias.*'),
                                'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text' => !request()->routeIs('categorias.*'),
                            ])
                        >
                            <x-app.icon name="tag" :size="13" />
                            <span class="nav-label">Categorias</span>
                        </a>
                        <a href="{{ route('centros-custo.index') }}"
                            @class([
                                'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[14px] transition-colors',
                                'text-mv-accent font-medium bg-mv-accent-soft' => request()->routeIs('centros-custo.*'),
                                'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text' => !request()->routeIs('centros-custo.*'),
                            ])
                        >
                            <x-app.icon name="building" :size="13" />
                            <span class="nav-label">Centros de Custo</span>
                        </a>
                        <a href="{{ route('usuarios') }}"
                            @class([
                                'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[14px] transition-colors',
                                'text-mv-accent font-medium bg-mv-accent-soft' => request()->routeIs('usuarios'),
                                'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text' => !request()->routeIs('usuarios'),
                            ])
                        >
                            <x-app.icon name="users" :size="13" />
                            <span class="nav-label">Usuários</span>
                        </a>
                        <a href="{{ route('funcionarios.index') }}"
                            @class([
                                'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[14px] transition-colors',
                                'text-mv-accent font-medium bg-mv-accent-soft' => request()->routeIs('funcionarios.*'),
                                'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text' => !request()->routeIs('funcionarios.*'),
                            ])
                        >
                            <x-app.icon name="user" :size="13" />
                            <span class="nav-label">Funcionários</span>
                        </a>
                        <a href="{{ route('unidades.index') }}"
                            @class([
                                'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[14px] transition-colors',
                                'text-mv-accent font-medium bg-mv-accent-soft' => request()->routeIs('unidades.*'),
                                'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text' => !request()->routeIs('unidades.*'),
                            ])
                        >
                            <x-app.icon name="ruler" :size="13" />
                            <span class="nav-label">Unidades de Medida</span>
                        </a>
                        <a href="{{ route('etiquetas.index') }}"
                            @class([
                                'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-[14px] transition-colors',
                                'text-mv-accent font-medium bg-mv-accent-soft' => request()->routeIs('etiquetas.*'),
                                'text-mv-text-secondary hover:bg-white/5 hover:text-mv-text' => !request()->routeIs('etiquetas.*'),
                            ])
                        >
                            <x-app.icon name="scan" :size="13" />
                            <span class="nav-label">Etiquetas</span>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- User bottom --}}
            <div class="flex-shrink-0 border-t border-mv-border p-2">
                <div class="mb-1 flex items-center gap-2 rounded-md px-2.5 py-1.5">
                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-mv-accent text-[13px] font-bold text-white">{{ $initials }}</div>
                    <div class="sidebar-user-info min-w-0">
                        <p class="truncate text-xs font-medium text-mv-text leading-tight">{{ $user->name }}</p>
                        <p class="text-[13px] text-mv-text-muted leading-tight">{{ $role }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            {{-- Topbar --}}
            <header class="flex h-16 flex-shrink-0 items-center gap-3 border-b border-mv-border bg-mv-surface px-5">
                <button id="sidebar-toggle" class="text-mv-text-secondary md:hidden">
                    <x-app.icon name="menu" :size="18" />
                </button>
                <div>
                    <p class="m-0 text-sm font-semibold leading-tight text-mv-text">{{ $pageTitle }}</p>
                    <p class="m-0 text-[13px] text-mv-text-muted leading-tight">Meu Estoque — São Mateus</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <div class="flex h-[30px] w-[30px] items-center justify-center rounded-full bg-mv-accent text-[13px] font-bold text-white">{{ $initials }}</div>
                    <span class="hidden text-[14px] text-mv-text-secondary sm:inline">{{ $user->name }}</span>
                    <div class="h-5 w-px bg-mv-border"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="group flex items-center gap-1.5 rounded-lg bg-mv-danger-solid px-3 py-1.5 text-[13px] font-medium text-white transition-colors hover:opacity-90" title="Sair">
                            <x-app.icon name="logout" :size="17" />
                            <span>Sair</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Main scroll --}}
            <main class="flex-1 overflow-auto p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('app-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const collapseToggle = document.getElementById('sidebar-collapse-toggle');
        const configToggle = document.querySelector('[data-config-toggle]');
        const configSubmenu = document.getElementById('config-submenu');
        const COLLAPSE_KEY = 'sidebar-collapsed';

        function openSidebar() { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
        function closeSidebar() { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }
        if (toggle) toggle.addEventListener('click', openSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        if (localStorage.getItem(COLLAPSE_KEY) === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        if (collapseToggle) {
            collapseToggle.addEventListener('click', () => {
                const collapsed = document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem(COLLAPSE_KEY, collapsed ? '1' : '0');
            });
        }

        if (configToggle && configSubmenu) {
            configToggle.addEventListener('click', () => {
                configSubmenu.classList.toggle('hidden');
                const chevron = configToggle.querySelector('svg:last-child');
                if (chevron) chevron.classList.toggle('rotate-180');
            });
        }

        (function () {
            document.addEventListener('click', (e) => {
                const trigger = e.target.closest('[data-image-zoom]');
                if (!trigger) return;

                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center bg-black/75 p-6';
                overlay.setAttribute('data-image-zoom-overlay', '');
                overlay.innerHTML =
                    '<img src="' + trigger.dataset.src + '" alt="' + (trigger.dataset.alt || '') + '" style="width:400px;height:400px;object-fit:cover" class="rounded-lg shadow-2xl">';
                overlay.addEventListener('click', () => overlay.remove());
                document.body.appendChild(overlay);
            });
        })();
    </script>
    <script src="{{ asset('vendor/bladewind/js/helpers.js') }}"></script>
</body>
</html>