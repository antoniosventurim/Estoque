<x-app.layout>
    <x-app.page-header
        title="Dashboard"
        subtitle="Visão geral do estoque"
        icon="dashboard"
    />

    @if ($error = session('error'))
        <x-app.card class="mb-4 p-4" style="border-color: #e8334a55; background: #8f0b2022">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-mv-danger-solid text-white">
                    <x-app.icon name="warning" :size="16" />
                </div>
                <p class="mt-1 text-[14px] font-semibold text-mv-danger">{{ $error }}</p>
            </div>
        </x-app.card>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3.5">
        <x-app.card class="p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1.5 text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Total de Produtos</p>
                    <p class="text-[26px] font-bold leading-none text-mv-accent">{{ $totalProducts }}</p>
                </div>
                <x-app.icon name="box" size="18" class="text-mv-accent opacity-70" />
            </div>
        </x-app.card>
        <x-app.card class="p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1.5 text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Itens em Estoque</p>
                    <p class="text-[26px] font-bold leading-none text-mv-success">{{ $totalStock }}</p>
                </div>
                <x-app.icon name="clipboard" size="18" class="text-mv-success opacity-70" />
            </div>
        </x-app.card>
        <x-app.card class="p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1.5 text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Estoque Baixo</p>
                    <p class="text-[26px] font-bold leading-none text-mv-warning">{{ $lowStock->count() }}</p>
                </div>
                <x-app.icon name="warning" size="18" class="text-mv-warning opacity-70" />
            </div>
        </x-app.card>
        <x-app.card class="p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1.5 text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Próximos ao Mínimo</p>
                    <p class="text-[26px] font-bold leading-none text-mv-warning">{{ $nearMinStock->count() }}</p>
                </div>
                <x-app.icon name="chart" size="18" class="text-mv-warning opacity-70" />
            </div>
        </x-app.card>
        <x-app.card class="p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="mb-1.5 text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Saídas Hoje</p>
                    <p class="text-[26px] font-bold leading-none text-mv-danger">{{ $todayOut }}</p>
                </div>
                <x-app.icon name="arrow-down" size="18" class="text-mv-danger opacity-70" />
            </div>
        </x-app.card>
    </div>

    {{-- Produtos com estoque baixo (críticos) --}}
    <x-app.card class="mb-5 overflow-hidden">
        <div class="flex items-center gap-2 border-b border-mv-border px-4.5 py-3.5">
            <span class="text-mv-warning"><x-app.icon name="warning" size="16" /></span>
            <h2 class="m-0 text-sm font-semibold text-mv-text">Produtos com Estoque Baixo</h2>
            <span class="ml-auto rounded-full bg-mv-warning-bg px-2 py-0.5 text-[13px] font-semibold text-mv-warning">{{ $lowStock->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border">
                        @foreach (['Produto', 'Código', 'Estoque Atual', 'Estoque Mínimo', 'Status'] as $h)
                            <th class="whitespace-nowrap px-4.5 py-2 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lowStock as $p)
                        <tr class="border-b border-mv-border">
                            <td class="px-4.5 py-2.5 font-medium text-mv-text">{{ $p->name }}</td>
                            <td class="mono px-4.5 py-2.5 text-xs text-mv-text-secondary">{{ $p->barcode }}</td>
                            <td class="px-4.5 py-2.5 font-semibold {{ $p->stock == 0 ? 'text-mv-danger' : 'text-mv-warning' }}">{{ $p->stock }} {{ $p->unit }}</td>
                            <td class="px-4.5 py-2.5 text-mv-text-secondary">{{ $p->min_stock }} {{ $p->unit }}</td>
                            <td class="px-4.5 py-2.5"><x-app.stock-badge :stock="$p->stock" :min-stock="$p->min_stock" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4.5 py-8 text-center text-[14px] text-mv-text-muted">Nenhum produto com estoque baixo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-app.card>

    {{-- Produtos próximos ao estoque mínimo --}}
    <x-app.card class="mb-5 overflow-hidden">
        <div class="flex items-center gap-2 border-b border-mv-border px-4.5 py-3.5">
            <span class="text-mv-warning"><x-app.icon name="chart" size="16" /></span>
            <h2 class="m-0 text-sm font-semibold text-mv-text">Próximos ao Estoque Mínimo</h2>
            <span class="ml-auto rounded-full bg-mv-warning-bg px-2 py-0.5 text-[13px] font-semibold text-mv-warning">{{ $nearMinStock->count() }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border">
                        @foreach (['Produto', 'Código', 'Estoque Atual', 'Estoque Mínimo', 'Status'] as $h)
                            <th class="whitespace-nowrap px-4.5 py-2 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nearMinStock as $p)
                        <tr class="border-b border-mv-border">
                            <td class="px-4.5 py-2.5 font-medium text-mv-text">{{ $p->name }}</td>
                            <td class="mono px-4.5 py-2.5 text-xs text-mv-text-secondary">{{ $p->barcode }}</td>
                            <td class="px-4.5 py-2.5 font-semibold text-mv-warning">{{ $p->stock }} {{ $p->unit }}</td>
                            <td class="px-4.5 py-2.5 text-mv-text-secondary">{{ $p->min_stock }} {{ $p->unit }}</td>
                            <td class="px-4.5 py-2.5"><x-app.stock-badge :stock="$p->stock" :min-stock="$p->min_stock" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4.5 py-8 text-center text-[14px] text-mv-text-muted">Nenhum produto próximo ao estoque mínimo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-app.card>

    {{-- Últimas movimentações --}}
    <x-app.card class="overflow-hidden">
        <div class="border-b border-mv-border px-4.5 py-3.5">
            <h2 class="m-0 text-sm font-semibold text-mv-text">Últimas Movimentações</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border">
                        @foreach (['Produto', 'Tipo', 'Qtd', 'Funcionário', 'Data/Hora'] as $h)
                            <th class="whitespace-nowrap px-4.5 py-2 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestMovements as $m)
                        <tr class="border-b border-mv-border">
                            <td class="px-4.5 py-2.5 font-medium text-mv-text">{{ $m->product->name ?? '—' }}</td>
                            <td class="px-4.5 py-2.5"><x-app.badge :type="$m->type" /></td>
                            <td class="px-4.5 py-2.5 font-bold {{ $m->type === 'in' ? 'text-mv-success' : ($m->type === 'out' ? 'text-mv-danger' : 'text-mv-warning') }}">
                                {{ $m->type === 'in' ? '+' : ($m->type === 'out' ? '−' : '±') }}{{ $m->quantity }}
                            </td>
                            <td class="px-4.5 py-2.5 text-mv-text-secondary">{{ $m->employee->name ?? '—' }}</td>
                            <td class="mono px-4.5 py-2.5 text-xs text-mv-text-secondary">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4.5 py-8 text-center text-[14px] text-mv-text-muted">Nenhuma movimentação registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-app.card>
</x-app.layout>