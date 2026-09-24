<x-app.layout>
    <x-app.page-header
        title="Lista de Compra"
        subtitle="{{ $products->count() }} item(ns) abaixo do estoque mínimo"
        icon="clipboard"
    >
        <x-app.btn as="a" href="{{ route('dashboard') }}" variant="ghost">Voltar</x-app.btn>
        <x-app.btn as="a" href="{{ route('lista-compra.export') }}" icon="download">Baixar Excel</x-app.btn>
    </x-app.page-header>

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
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-[14px] text-mv-text-muted">Nenhum produto encontrado.</td></tr>
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
</x-app.layout>
