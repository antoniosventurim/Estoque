<x-app.layout>
    <x-app.page-header
        title="Movimentações"
        subtitle="{{ $movements->total() }} registros no histórico"
        icon="list"
    />

    {{-- Filtros --}}
    <form method="GET" action="{{ route('movimentacoes') }}" class="mb-4 flex flex-wrap items-center gap-2.5">
        <div class="relative min-w-[200px] flex-1">
            <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="14" /></div>
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar produto..."
                class="w-full rounded-md border border-mv-border bg-mv-surface py-2 pl-8 pr-2.5 text-[14px] text-mv-text outline-none focus:border-blue-600">
        </div>
        <div class="relative w-[180px]">
            <x-app.searchable-select
                name="type"
                placeholder="Tipo"
                :selected="$type"
                :selected-label="$type === 'in' ? 'Entrada' : ($type === 'out' ? 'Saída' : ($type === 'adjust' ? 'Ajuste' : ''))"
                :options="collect([['id' => 'in', 'name' => 'Entrada', 'sub' => ''], ['id' => 'out', 'name' => 'Saída', 'sub' => ''], ['id' => 'adjust', 'name' => 'Ajuste', 'sub' => '']])->values()->all()"
            />
        </div>
        <div class="relative w-[180px]">
            <x-app.searchable-select
                name="employee"
                placeholder="Funcionário"
                :selected="$employee"
                :selected-label="$employee ? ($employees->firstWhere('id', $employee)->name ?? '') : ''"
                :options="$employees->map(fn ($e) => ['id' => $e->id, 'name' => $e->name, 'sub' => ''])->values()->all()"
            />
        </div>
        <div class="relative w-[180px]">
            <x-app.searchable-select
                name="cost_center"
                placeholder="Centro de custo"
                :selected="$costCenter"
                :selected-label="$costCenter ? ($costCenters->firstWhere('id', $costCenter)->name ?? '') : ''"
                :options="$costCenters->map(fn ($cc) => ['id' => $cc->id, 'name' => $cc->name, 'sub' => ''])->values()->all()"
            />
        </div>
        <x-app.btn type="submit" icon="search" variant="secondary">Filtrar</x-app.btn>
        @if ($type !== '' || $employee !== '' || $costCenter !== '' || $search !== '')
            <x-app.btn as="a" href="{{ route('movimentacoes') }}" variant="ghost">Limpar</x-app.btn>
        @endif
    </form>

    <x-app.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border bg-mv-surface2">
                        @foreach (['#', 'Produto', 'Centro de Custo', 'Tipo', 'Qtd', 'Est. Anterior', 'Est. Atual', 'Responsável', 'Data/Hora'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $i => $m)
                        <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $movements->firstItem() + $i }}</td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center gap-2.5">
                                    @if ($m->product?->image)
                                        <img src="{{ asset('storage/'.$m->product->image) }}" alt="" class="h-8 w-8 flex-shrink-0 rounded object-cover">
                                    @else
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded bg-mv-surface2">
                                            <x-app.icon name="box" :size="14" class="text-mv-text-muted" />
                                        </div>
                                    @endif
                                    <span class="font-medium text-mv-text">{{ $m->product->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $m->costCenter->name ?? '—' }}</td>
                            <td class="px-3.5 py-2.5"><x-app.badge :type="$m->type" /></td>
                            <td class="px-3.5 py-2.5 font-bold {{ $m->type === 'in' ? 'text-mv-success' : ($m->type === 'out' ? 'text-mv-danger' : 'text-mv-warning') }}">
                                {{ $m->type === 'in' ? '+' : ($m->type === 'out' ? '−' : '±') }}{{ $m->quantity }}
                            </td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $m->stock_before }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text">{{ $m->stock_after }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">
                                {{ $m->type === 'in' ? ($m->user->name ?? '—') : ($m->employee->name ?? '—') }}
                            </td>
                            <td class="mono px-3.5 py-2.5 text-xs text-mv-text-secondary">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhuma movimentação encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-app.pagination :paginator="$movements" />
    </x-app.card>
</x-app.layout>