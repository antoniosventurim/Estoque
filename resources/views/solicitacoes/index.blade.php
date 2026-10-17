<x-app.layout>
    <x-app.page-header
        title="Solicitações de Compra"
        subtitle="{{ $requests->total() }} solicitação(ões) registrada(s)"
        icon="check"
    >
        <x-app.btn as="a" href="{{ route('lista-compra.index') }}" icon="paper">Lista de Compra</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap items-center gap-2.5">
        <form method="GET" action="{{ route('solicitacoes.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
            <div class="flex items-center gap-1.5">
                @foreach ([
                    'all' => ['label' => 'Todas', 'count' => $pendingCount + $partialCount + $deliveredCount],
                    'pending' => ['label' => 'Pendentes', 'count' => $pendingCount],
                    'partial' => ['label' => 'Parciais', 'count' => $partialCount],
                    'delivered' => ['label' => 'Recebidas', 'count' => $deliveredCount],
                ] as $key => $tab)
                    <a href="{{ route('solicitacoes.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
                        @class([
                            'rounded-md border px-3 py-1.5 text-[14px] transition-colors',
                            'border-mv-border bg-mv-surface2 text-mv-text font-medium' => $status === $key,
                            'border-mv-border text-mv-text-secondary hover:text-mv-text' => $status !== $key,
                        ])
                    >
                        {{ $tab['label'] }}
                        <span class="ml-1 text-xs {{ $status === $key ? 'text-mv-text-secondary' : 'text-mv-text-muted' }}">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="relative min-w-[240px] flex-1">
                <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="14" /></div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nome do produto..."
                    class="w-full rounded-md border border-mv-border bg-mv-surface py-2 pl-8 pr-2.5 text-[14px] text-mv-text outline-none">
            </div>
            <input type="hidden" name="status" value="{{ $status }}">
            <x-app.btn type="submit" icon="search" variant="secondary">Filtrar</x-app.btn>
        </form>
    </div>

    {{-- Tabela --}}
    <x-app.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border bg-mv-surface2">
                        @foreach (['#', 'Itens', 'Solicitado Por', 'Data da Solicitação', 'Recebimento', 'Status', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 {{ in_array($h, ['Itens', 'Ações']) ? 'text-center' : 'text-left' }} text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $i => $r)
                        <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $requests->firstItem() + $i }}</td>
                            <td class="px-3.5 py-2.5 text-center font-semibold text-mv-text">{{ $r->items_count }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $r->user->name ?? '—' }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $r->delivered_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-3.5 py-2.5">
                                @if ($r->status === \App\Models\PurchaseRequest::STATUS_DELIVERED)
                                    <span class="rounded-full bg-green-500/10 px-2 py-0.5 text-[13px] font-semibold text-green-500">Recebida</span>
                                @elseif ($r->status === \App\Models\PurchaseRequest::STATUS_PARTIAL)
                                    <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-[13px] font-semibold text-blue-400">Parcial</span>
                                @else
                                    <span class="rounded-full bg-mv-warning-bg px-2 py-0.5 text-[13px] font-semibold text-mv-warning">Pendente</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('solicitacoes.show', $r) }}" size="sm" variant="secondary" icon="search">Detalhes</x-app.btn>
                                    @if ($r->isReceivable())
                                        <x-app.btn type="button" size="sm" icon="check" data-open-receive="{{ $r->id }}">Recebida</x-app.btn>
                                    @endif
                                    @if ($r->isPending())
                                        <form method="POST" action="{{ route('solicitacoes.destroy', $r) }}" onsubmit="return confirm('Excluir esta solicitação?')">
                                            @csrf @method('DELETE')
                                            <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhuma solicitação encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-app.pagination :paginator="$requests" />
    </x-app.card>

    @foreach ($requests as $r)
        @if ($r->isReceivable())
            <x-app.receive-modal :purchaseRequest="$r" />
        @endif
    @endforeach
</x-app.layout>
