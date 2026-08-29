<x-app.layout>
    <x-app.page-header
        title="Relatórios"
        subtitle="Visão geral das operações de estoque"
        icon="chart"
    />

    {{-- Filtro por período --}}
    <form method="GET" action="{{ route('relatorios') }}" class="mb-5 flex flex-wrap items-end gap-2.5">
        <div class="flex flex-col">
            <label class="mb-1 text-xs text-mv-text-secondary">De</label>
            <div class="w-[160px]">
                <x-bladewind::datepicker name="from" :selected-value="$from" placeholder="De" label="" week-starts="monday" format="dd-mm-yyyy" />
            </div>
        </div>
        <div class="flex flex-col">
            <label class="mb-1 text-xs text-mv-text-secondary">Até</label>
            <div class="w-[160px]">
                <x-bladewind::datepicker name="to" :selected-value="$to" placeholder="Até" label="" week-starts="monday" format="dd-mm-yyyy" />
            </div>
        </div>
        <x-app.btn type="submit" icon="filter" variant="secondary">Aplicar</x-app.btn>
        @if ($from || $to)
            <x-app.btn as="a" href="{{ route('relatorios') }}" variant="ghost">Limpar</x-app.btn>
        @endif
    </form>

    {{-- Cards resumo --}}
    <div class="mb-5 grid grid-cols-[repeat(auto-fit,minmax(150px,1fr))] gap-3">
        @foreach ([
            ['label' => 'Entradas (itens)', 'value' => $totalEntradas, 'color' => 'text-mv-success'],
            ['label' => 'Saídas (itens)', 'value' => $totalSaidas, 'color' => 'text-mv-danger'],
            ['label' => 'Ajustes realizados', 'value' => $totalAjustes, 'color' => 'text-mv-warning'],
            ['label' => 'Total movimentações', 'value' => $totalMovimentacoes, 'color' => 'text-mv-text-secondary'],
            ['label' => 'Estoque crítico', 'value' => $critical->count(), 'color' => 'text-mv-danger'],
            ['label' => 'Próximos ao mínimo', 'value' => $approaching->count(), 'color' => 'text-mv-warning'],
        ] as $s)
            <x-app.card class="p-3.5">
                <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">{{ $s['label'] }}</p>
                <p class="m-0 text-[22px] font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
            </x-app.card>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">

        {{-- Produtos mais retirados --}}
        <x-app.card class="overflow-hidden">
            <div class="flex items-center justify-between border-b border-mv-border px-4.5 py-3.5">
                <h2 class="m-0 text-[14px] font-semibold text-mv-text">Produtos Mais Retirados</h2>
                <span class="text-[13px] text-mv-text-muted">{{ $top->count() }} produto(s)</span>
            </div>
            <div class="p-4.5">
                @if ($top->isEmpty())
                    <p class="py-4 text-center text-[14px] text-mv-text-muted">Nenhuma saída registrada.</p>
                @else
                    <div class="relative h-[320px]">
                        <canvas data-outflow-chart="outflow-data"></canvas>
                    </div>
                    <script type="application/json" id="outflow-data">
                        {!! json_encode([
                            'labels' => $top->map(fn ($r) => $r['product']->name)->values(),
                            'data' => $top->map(fn ($r) => $r['total'])->values(),
                        ]) !!}
                    </script>
                @endif
            </div>
        </x-app.card>

        {{-- Estoque crítico --}}
        <x-app.card class="overflow-hidden">
            <div class="flex items-center gap-2 border-b border-mv-border px-4.5 py-3.5">
                <span class="text-mv-danger"><x-app.icon name="warning" :size="16" /></span>
                <h2 class="m-0 text-[14px] font-semibold text-mv-text">Estoque Crítico / Zerado</h2>
                <span class="ml-auto rounded-full bg-mv-danger-bg px-2 py-0.5 text-[13px] font-semibold text-mv-danger">{{ $critical->count() }}</span>
            </div>
            @if ($critical->isEmpty())
                <p class="px-4.5 py-5 text-[14px] text-mv-success">Nenhum produto em estado crítico.</p>
            @else
                <div>
                    @foreach ($critical as $p)
                        <div class="flex items-center justify-between border-b border-mv-border px-4.5 py-2.5 last:border-0">
                            <div>
                                <p class="m-0 text-xs font-medium text-mv-text">{{ $p->name }}</p>
                                <p class="mono m-0 text-[13px] text-mv-text-muted">{{ $p->barcode }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-mv-danger">{{ $p->stock }} / {{ $p->min_stock }} {{ $p->unit }}</span>
                                <x-app.stock-badge :stock="$p->stock" :min-stock="$p->min_stock" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-app.card>

        {{-- Aproximando do mínimo --}}
        <x-app.card class="overflow-hidden">
            <div class="flex items-center gap-2 border-b border-mv-border px-4.5 py-3.5">
                <span class="text-mv-warning"><x-app.icon name="warning" :size="16" /></span>
                <h2 class="m-0 text-[14px] font-semibold text-mv-text">Aproximando do Mínimo</h2>
                <span class="ml-auto rounded-full bg-mv-warning-bg px-2 py-0.5 text-[13px] font-semibold text-mv-warning">{{ $approaching->count() }}</span>
            </div>
            @if ($approaching->isEmpty())
                <p class="px-4.5 py-5 text-[14px] text-mv-success">Nenhum produto se aproximando do mínimo.</p>
            @else
                <div>
                    @foreach ($approaching as $p)
                        @php $pct = $p->min_stock > 0 ? round(($p->stock / $p->min_stock) * 100) : 0; @endphp
                        <div class="border-b border-mv-border px-4.5 py-2.5 last:border-0">
                            <div class="mb-1 flex items-center justify-between">
                                <div>
                                    <p class="m-0 text-xs font-medium text-mv-text">{{ $p->name }}</p>
                                    <p class="mono m-0 text-[13px] text-mv-text-muted">{{ $p->barcode }}</p>
                                </div>
                                <p class="m-0 text-xs font-bold text-mv-warning">{{ $p->stock }} {{ $p->unit }} <span class="font-normal text-mv-text-muted">(mín: {{ $p->min_stock }}, {{ $pct }}%)</span></p>
                            </div>
                            <div class="h-1 overflow-hidden rounded bg-mv-surface2">
                                <div class="h-full rounded" style="width: {{ min($pct, 150) > 100 ? 100 : min($pct, 150) }}%; background:#e09b2a"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-app.card>

        {{-- Atividade por funcionário --}}
        <x-app.card class="overflow-hidden">
            <div class="border-b border-mv-border px-4.5 py-3.5">
                <h2 class="m-0 text-[14px] font-semibold text-mv-text">Atividade por Funcionário</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[14px]">
                    <thead>
                        <tr class="border-b border-mv-border bg-mv-surface2">
                            @foreach (['Funcionário', 'Entradas', 'Saídas', 'Total ops.'] as $h)
                                <th class="whitespace-nowrap px-4 py-2 text-left text-[13px] uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($byEmployee as $e)
                            <tr class="border-b border-mv-border">
                                <td class="px-4 py-2.5 font-medium text-mv-text">{{ $e['name'] }}</td>
                                <td class="px-4 py-2.5 font-semibold text-mv-success">+{{ $e['entradas'] }}</td>
                                <td class="px-4 py-2.5 font-semibold text-mv-danger">−{{ $e['saidas'] }}</td>
                                <td class="px-4 py-2.5 text-mv-text-secondary">{{ $e['ops'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-[14px] text-mv-text-muted">Sem movimentações no período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-app.card>
    </div>
</x-app.layout>