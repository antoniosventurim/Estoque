@php
    $newProducts = $products->filter(function ($p) use ($pendingProductIds) {
        return ! $pendingProductIds->contains($p->id);
    })->values();
@endphp

<x-app.layout>
    <x-app.page-header
        title="Lista de Compra"
        subtitle="{{ $products->count() }} item(ns) abaixo do estoque mínimo"
        icon="clipboard"
    >
        <x-app.btn as="a" href="{{ route('solicitacoes.index') }}" icon="check">Acompanhar Solicitações</x-app.btn>
        <x-app.btn as="a" href="{{ route('lista-compra.export') }}" icon="download">Baixar Excel</x-app.btn>
        @if ($newProducts->isNotEmpty())
            <x-app.btn type="button" icon="paper" data-open-generate>Gerar Solicitação</x-app.btn>
        @endif
    </x-app.page-header>

    <x-app.flash />
    <x-app.errors />

    @if ($products->isEmpty())
        <x-app.card class="p-8">
            <div class="text-center text-mv-text-muted">
                <x-app.icon name="check-circle" :size="40" class="mx-auto mb-3 text-green-500" />
                <p class="text-[15px] font-medium">Todos os produtos estão com estoque acima do mínimo.</p>
            </div>
        </x-app.card>
    @else
        <x-app.card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-[14px]">
                    <thead>
                        <tr class="border-b border-mv-border bg-mv-surface2">
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">#</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Produto</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Categoria</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Unidade</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Estoque Atual</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Estoque Mínimo</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Estoque Máximo</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Quantidade a Pedir</th>
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $i => $p)
                            <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                                <td class="px-4 py-3 text-mv-text-secondary">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-mv-text">{{ $p->name }}</td>
                                <td class="px-4 py-3 text-mv-text-secondary">{{ $p->category->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $p->unitModel->name ?? $p->unit }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-mv-danger">{{ $p->stock }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $p->min_stock }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $p->max_stock }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $p->quantity_to_order }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($pendingProductIds->contains($p->id))
                                        <span class="rounded-full bg-mv-warning-bg px-2 py-0.5 text-[13px] font-semibold text-mv-warning">Aguardando recebimento</span>
                                    @else
                                        <span class="text-mv-text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-10 text-center text-[14px] text-mv-text-muted">Nenhum produto encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-mv-border px-4 py-3">
                <p class="text-xs text-mv-text-muted">
                    Total de itens para reposição: <span class="font-semibold text-mv-text">{{ $products->count() }}</span>
                </p>
            </div>
        </x-app.card>
    @endif

    <style>
        @media print {
            #app-sidebar, header, .btn, [x-app\\.btn] { display: none !important; }
            main { padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
        }
    </style>

    @if ($newProducts->isNotEmpty())
        {{-- Modal: gerar solicitação --}}
        <div id="generate-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-lg rounded-lg border border-mv-border bg-mv-surface shadow-2xl">
                <div class="border-b border-mv-border px-5 py-4">
                    <h3 class="m-0 text-[15px] font-semibold text-mv-text">Gerar Solicitação de Compra</h3>
                    <p class="mt-1 text-[14px] text-mv-text-secondary">
                        Os {{ $newProducts->count() }} item(ns) abaixo ainda não foram solicitados.
                        A quantidade padrão é a que falta para o estoque máximo — ajuste se quiser pedir menos, ou zere para não incluir o item.
                    </p>
                </div>

                <form method="POST" action="{{ route('solicitacoes.store') }}">
                    @csrf

                    <div class="max-h-[320px] overflow-y-auto">
                        <table class="w-full border-collapse text-[14px]">
                            <thead>
                                <tr class="border-b border-mv-border bg-mv-surface2">
                                    <th class="px-4 py-2 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Produto</th>
                                    <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Estoque</th>
                                    <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">A Pedir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($newProducts as $p)
                                    @php
                                        $defaultQty = $p->quantity_to_order > 0 ? $p->quantity_to_order : max(1, $p->min_stock - $p->stock);
                                    @endphp
                                    <tr class="border-b border-mv-border">
                                        <td class="px-4 py-2.5 text-mv-text">{{ $p->name }}</td>
                                        <td class="px-4 py-2.5 text-center font-semibold text-mv-danger">{{ $p->stock }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <input type="number" name="quantities[{{ $p->id }}]" value="{{ $defaultQty }}" min="0" max="{{ $defaultQty }}" step="1"
                                                class="w-20 rounded-md border border-mv-border bg-mv-surface2 px-2 py-1.5 text-center text-[14px] font-semibold text-green-600 outline-none">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($pendingProductIds->isNotEmpty())
                        <div class="border-t border-mv-border px-5 py-3 text-[13px] text-mv-text-muted">
                            Itens já aguardando recebimento em outras solicitações não serão duplicados.
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 border-t border-mv-border px-5 py-3">
                        <button type="button" data-generate-cancel class="rounded-md border border-mv-border bg-mv-surface2 px-4 py-2 text-[14px] font-medium text-mv-text-secondary transition-colors hover:border-mv-border-hover">Cancelar</button>
                        <button type="submit" class="rounded-md bg-mv-accent px-4 py-2 text-[14px] font-medium text-white transition-opacity hover:opacity-90">Confirmar e Gerar</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('generate-modal');
                if (!modal) return;

                function openModal() {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                document.querySelectorAll('[data-open-generate]').forEach(function (btn) {
                    btn.addEventListener('click', openModal);
                });

                modal.querySelector('[data-generate-cancel]').addEventListener('click', closeModal);

                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });
            })();
        </script>
    @endif
</x-app.layout>
