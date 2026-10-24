<x-app.layout>
    <x-app.page-header
        title="Solicitação #{{ $purchaseRequest->id }}"
        subtitle="Solicitada em {{ $purchaseRequest->created_at->format('d/m/Y H:i') }} por {{ $purchaseRequest->user->name ?? '—' }}"
        icon="check"
    >
        <x-app.btn as="a" href="{{ route('solicitacoes.index') }}" variant="back">Voltar</x-app.btn>
        @if ($purchaseRequest->isReceivable())
            <x-app.btn type="button" icon="check" data-open-receive="{{ $purchaseRequest->id }}">
                {{ $purchaseRequest->isPartial() ? 'Receber Restante' : 'Marcar como Recebida' }}
            </x-app.btn>
        @endif
    </x-app.page-header>

    <x-app.flash />

    <x-app.card class="mb-5 p-4">
        <div class="grid grid-cols-2 gap-x-6 gap-y-4 text-[14px] sm:flex sm:flex-wrap sm:items-center sm:gap-x-8 sm:gap-y-2">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-mv-text-secondary">Status</p>
                @if ($purchaseRequest->status === \App\Models\PurchaseRequest::STATUS_DELIVERED)
                    <span class="rounded-full bg-green-500/10 px-2 py-0.5 text-[13px] font-semibold text-green-500">Recebida</span>
                @elseif ($purchaseRequest->status === \App\Models\PurchaseRequest::STATUS_PARTIAL)
                    <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-[13px] font-semibold text-blue-400">Parcial</span>
                @else
                    <span class="rounded-full bg-mv-warning-bg px-2 py-0.5 text-[13px] font-semibold text-mv-warning">Pendente</span>
                @endif
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-mv-text-secondary">Data da Solicitação</p>
                <p class="text-mv-text">{{ $purchaseRequest->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-mv-text-secondary">Data do Recebimento</p>
                <p class="text-mv-text">{{ $purchaseRequest->delivered_at?->format('d/m/Y H:i') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-mv-text-secondary">Recebido Por</p>
                <p class="text-mv-text">{{ $purchaseRequest->deliveredBy->name ?? '—' }}</p>
            </div>
            @if ($purchaseRequest->note)
                <div class="col-span-2 sm:col-span-1">
                    <p class="text-xs font-medium uppercase tracking-wider text-mv-text-secondary">Observação</p>
                    <p class="text-mv-text">{{ $purchaseRequest->note }}</p>
                </div>
            @endif
        </div>
        @if ($purchaseRequest->isPartial())
            @php
                $missingItems = $purchaseRequest->items->filter(fn ($item) => ! $item->isFullyReceived());
            @endphp
            <div class="mt-3 rounded-md border border-blue-500/30 bg-blue-500/10 p-3 text-[14px] text-blue-400">
                Recebimento parcial registrado. Faltam
                <span class="font-semibold">{{ $missingItems->count() }}</span> item(ns)
                ({{ $missingItems->sum('missing_quantity') }} unidade(s) no total) aguardando recebimento.
            </div>
        @endif
    </x-app.card>

    @php
        $missingItems = $purchaseRequest->items->filter(fn ($item) => ! $item->isFullyReceived());
        $activeTab = request()->query('tab') === 'faltantes' && $missingItems->isNotEmpty() ? 'faltantes' : 'itens';
    @endphp

    @if ($missingItems->isNotEmpty())
        <div class="mb-4 flex items-center gap-1.5">
            @foreach ([
                'itens' => 'Todos os Itens',
                'faltantes' => 'Faltantes (' . $missingItems->count() . ')',
            ] as $key => $label)
                <a href="{{ route('solicitacoes.show', [$purchaseRequest, 'tab' => $key]) }}"
                    @class([
                        'rounded-md border px-3 py-1.5 text-[14px] transition-colors',
                        'border-mv-border bg-mv-surface2 text-mv-text font-medium' => $activeTab === $key,
                        'border-mv-border text-mv-text-secondary hover:text-mv-text' => $activeTab !== $key,
                    ])
                >{{ $label }}</a>
            @endforeach
        </div>
    @endif

    @if ($activeTab === 'faltantes')
        <x-app.card class="overflow-hidden">
            <div class="border-b border-mv-border bg-mv-surface2 px-4 py-3 text-[14px] text-mv-text-secondary">
                Itens ainda não recebidos desta solicitação. Ao confirmar, as quantidades abaixo entram no estoque e a solicitação é finalizada como <strong>Recebida</strong>.
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[14px]">
                    <thead>
                        <tr class="border-b border-mv-border bg-mv-surface2">
                            @foreach (['#', 'Produto', 'Unidade', 'Solicitado', 'Recebido', 'Faltando'] as $h)
                                <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($missingItems as $i => $item)
                            <tr class="border-b border-mv-border hover:bg-black/[0.02] dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3 text-center text-mv-text-muted">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-left font-medium text-mv-text">{{ $item->product->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $item->product->unitModel->name ?? $item->product->unit ?? '—' }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-mv-text-secondary">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $item->received_quantity }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-mv-danger">{{ $item->missing_quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end border-t border-mv-border px-4 py-3">
                <form method="POST" action="{{ route('solicitacoes.deliver', $purchaseRequest) }}" onsubmit="return confirm('Receber o restante? O estoque será atualizado e a solicitação será finalizada.')">
                    @csrf
                    <x-app.btn type="submit" icon="check">Receber Restante e Finalizar</x-app.btn>
                </form>
            </div>
        </x-app.card>
    @else
        <x-app.card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[14px]">
                    <thead>
                        <tr class="border-b border-mv-border bg-mv-surface2">
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">#</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Produto</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Categoria</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Unidade</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Solicitado</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Recebido</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Faltando</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($purchaseRequest->items as $i => $item)
                            <tr class="border-b border-mv-border hover:bg-black/[0.02] dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3 text-center text-mv-text-muted">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-mv-text">{{ $item->product->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-mv-text-secondary">{{ $item->product->category->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $item->product->unitModel->name ?? $item->product->unit ?? '—' }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-mv-text-secondary">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $item->received_quantity }}</td>
                                <td class="px-4 py-3 text-center font-semibold {{ $item->missing_quantity > 0 ? 'text-mv-danger' : 'text-mv-text-muted' }}">{{ $item->missing_quantity }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-[14px] text-mv-text-muted">Nenhum item nesta solicitação.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-mv-border px-4 py-3">
                <p class="text-xs text-mv-text-muted">
                    Total de itens: <span class="font-semibold text-mv-text">{{ $purchaseRequest->items->count() }}</span>
                    · Recebidos: <span class="font-semibold text-green-600">{{ $purchaseRequest->items->filter(fn ($item) => $item->isFullyReceived())->count() }}</span>
                    · Faltando: <span class="font-semibold {{ $missingItems->count() > 0 ? 'text-mv-danger' : 'text-mv-text' }}">{{ $missingItems->count() }}</span>
                </p>
            </div>
        </x-app.card>
    @endif

    @if ($purchaseRequest->isReceivable())
        <x-app.receive-modal :purchaseRequest="$purchaseRequest" />
    @endif
</x-app.layout>
