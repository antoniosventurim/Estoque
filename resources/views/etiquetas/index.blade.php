<x-app.layout>
    <x-app.page-header
        title="Etiquetas"
        subtitle="Selecione os produtos e imprima os códigos de barras"
        icon="scan"
    />

    <form method="GET" action="{{ route('etiquetas.index') }}" class="mb-4 flex flex-wrap items-end gap-2.5">
        <div class="relative min-w-[240px] flex-1">
            <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="14" /></div>
            <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nome ou código..."
                class="w-full rounded-md border border-mv-border bg-mv-surface py-2 pl-8 pr-2.5 text-[14px] text-mv-text outline-none">
        </div>
        <x-app.btn type="submit" icon="search" variant="secondary">Filtrar</x-app.btn>
        @if ($search)
            <x-app.btn as="a" href="{{ route('etiquetas.index') }}" variant="ghost">Limpar</x-app.btn>
        @endif
    </form>

    <form method="GET" action="{{ route('etiquetas.print') }}" target="_blank" id="print-form">
        <x-app.card class="overflow-hidden">
            <div class="flex items-center justify-between border-b border-mv-border px-4.5 py-3.5">
                <h2 class="m-0 text-[14px] font-semibold text-mv-text">Todos os códigos de barras</h2>
                <span class="text-[13px] text-mv-text-muted">{{ $products->count() }} produto(s)</span>
            </div>

            <div class="overflow-x-auto">
                @php
                    $sortUrl = fn ($col) => route('etiquetas.index', array_filter([
                        'q' => $search,
                        'sort' => $col,
                        'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc',
                    ]));
                    $arrow = fn ($col) => $sort === $col ? ($dir === 'asc' ? ' ↑' : ' ↓') : '';
                @endphp
                <table class="w-full border-collapse text-[14px]">
                    <thead>
                        <tr class="border-b border-mv-border bg-mv-surface2">
                            <th class="w-12 px-4 py-2.5">
                                <x-bladewind::checkbox name="select_all" class="select-all-check barcode-check" add_clearing="false" />
                            </th>
                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[13px] uppercase tracking-wider text-mv-text-secondary">
                                <a href="{{ $sortUrl('name') }}" class="hover:text-mv-text">Produto{{ $arrow('name') }}</a>
                            </th>
                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[13px] uppercase tracking-wider text-mv-text-secondary">
                                <a href="{{ $sortUrl('barcode') }}" class="hover:text-mv-text">Código de barras{{ $arrow('barcode') }}</a>
                            </th>
                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-[13px] uppercase tracking-wider text-mv-text-secondary">
                                <a href="{{ $sortUrl('stock') }}" class="hover:text-mv-text">Estoque{{ $arrow('stock') }}</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $p)
                            <tr class="border-b border-mv-border last:border-0">
                                <td class="px-4 py-2.5"><x-bladewind::checkbox name="ids[]" :value="$p->id" class="row-check barcode-check" add_clearing="false" /></td>
                                <td class="px-4 py-2.5 font-medium text-mv-text">{{ $p->name }}</td>
                                <td class="mono px-4 py-2.5 text-mv-text-secondary">{{ $p->barcode }}</td>
                                <td class="px-4 py-2.5 text-mv-text-muted">{{ $p->stock }} {{ $p->unit }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-[14px] text-mv-text-muted">
                                    Nenhum produto encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-mv-border px-4.5 py-3">
                <span id="selected-count" class="text-[13px] text-mv-text-secondary">0 selecionado(s)</span>
                <x-app.btn type="submit" icon="scan" variant="primary">Imprimir selecionados</x-app.btn>
            </div>
        </x-app.card>
    </form>

    <script>
        (() => {
            const selectAll = document.querySelector('.select-all-check');
            const boxes = Array.from(document.querySelectorAll('.row-check'));
            const countEl = document.getElementById('selected-count');

            const refresh = () => {
                const n = boxes.filter((b) => b.checked).length;
                if (countEl) countEl.textContent = `${n} selecionado(s)`;
            };

            selectAll.addEventListener('change', () => {
                boxes.forEach((b) => (b.checked = selectAll.checked));
                refresh();
            });

            boxes.forEach((b) => b.addEventListener('change', refresh));

            document.querySelectorAll('#print-form').forEach(() => {});
        })();
    </script>
</x-app.layout>