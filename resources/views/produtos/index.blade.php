<x-app.layout>
    <x-app.page-header
        title="Produtos"
        subtitle="{{ $products->total() }} produto(s) cadastrado(s)"
        icon="box"
    >
        <x-app.btn as="a" href="{{ route('produtos.create') }}" icon="plus">Novo Produto</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap items-center gap-2.5">
        <form method="GET" action="{{ route('produtos.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
            <div class="relative min-w-[240px] flex-1">
                <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="14" /></div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nome, SKU ou código de barras..."
                    class="w-full rounded-md border border-mv-border bg-mv-surface py-2 pl-8 pr-2.5 text-[14px] text-mv-text outline-none focus:border-blue-600">
            </div>
            <div class="relative w-[180px]">
                <x-app.searchable-select
                    name="status"
                    placeholder="Status"
                    :selected="$status"
                    :selected-label="$status === 'ativo' ? 'Ativo' : ($status === 'inativo' ? 'Inativo' : '')"
                    :options="collect([['id' => 'ativo', 'name' => 'Ativo', 'sub' => ''], ['id' => 'inativo', 'name' => 'Inativo', 'sub' => '']])->values()->all()"
                />
            </div>
            <div class="relative w-[180px]">
                <x-app.searchable-select
                    name="category"
                    placeholder="Categoria"
                    :selected="$categoryId"
                    :selected-label="$categoryId !== '' ? ($categories->firstWhere('id', (int) $categoryId)->name ?? '') : ''"
                    :options="$categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'sub' => ''])->values()->all()"
                />
            </div>
            <x-app.btn type="submit" icon="search" variant="secondary">Filtrar</x-app.btn>
        </form>
    </div>

    {{-- Tabela --}}
    <x-app.card class="overflow-hidden">
        <form method="POST" action="{{ route('produtos.bulkDestroy') }}" id="bulk-products">
            @csrf
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border bg-mv-surface2">
                        @foreach (['#', '', 'Nome', 'Cód. Barras', 'Categoria', 'Estoque', 'Mínimo', 'Status', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 {{ $h === 'Ações' ? 'text-center' : 'text-left' }} text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">
                                @if ($h === '#')
                                    <x-bladewind::checkbox name="select_all" title="Selecionar todos" class="select-all-check" add_clearing="false" />
                                @else
                                    {{ $h }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $i => $p)
                        <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5"><x-bladewind::checkbox name="ids[]" :value="$p->id" class="row-checkbox" add_clearing="false" /></td>
                            <td class="px-1 py-2.5"><div class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-md bg-mv-surface2 border border-mv-border">@if($p->image)<img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}" class="h-full w-full object-cover">@else<x-app.icon name="box" :size="15" class="text-mv-text-muted" />@endif</div></td>
                            <td class="px-3.5 py-2.5 font-medium text-mv-text">{{ $p->name }}</td>
                            <td class="mono px-3.5 py-2.5 text-xs text-mv-text-secondary">{{ $p->barcode }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $p->category->name ?? '—' }}</td>
                            <td class="px-3.5 py-2.5 font-semibold {{ $p->stock <= $p->min_stock ? 'text-mv-danger' : ($p->stock <= $p->min_stock * 1.5 ? 'text-mv-warning' : 'text-mv-text') }}">{{ $p->stock }} {{ $p->unit }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $p->min_stock }} {{ $p->unit }}</td>
                            <td class="px-3.5 py-2.5"><x-app.stock-badge :stock="$p->stock" :min-stock="$p->min_stock" /></td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('produtos.edit', $p) }}" size="sm" variant="secondary" icon="edit">Editar</x-app.btn>
                                    <form method="POST" action="{{ route('produtos.destroy', $p) }}" onsubmit="return confirm('Excluir este produto?')">
                                        @csrf @method('DELETE')
                                        <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhum produto encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="bulk-bar" class="hidden items-center justify-end gap-3 border-t border-mv-border px-4 py-3">
            <span id="selected-count" class="text-xs text-mv-text-muted">0 selecionado(s)</span>
            <x-app.btn type="submit" size="sm" variant="danger" icon="trash" onclick="return confirmBulkDelete()">Excluir selecionados</x-app.btn>
        </div>
        </form>

        <x-app.pagination :paginator="$products" />
    </x-app.card>

    <script>
        (function () {
            const selectAll = document.querySelector('.select-all-check');
            const boxes = Array.from(document.querySelectorAll('.row-checkbox'));
            const count = document.getElementById('selected-count');
            const bar = document.getElementById('bulk-bar');
            const update = () => {
                const n = boxes.filter(b => b.checked).length;
                if (count) count.textContent = n + ' selecionado(s)';
                if (bar) bar.classList.toggle('hidden', n === 0);
                if (bar) bar.classList.toggle('flex', n > 0);
            };

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    boxes.forEach(b => b.checked = selectAll.checked);
                    update();
                });
            }
            boxes.forEach(b => b.addEventListener('change', update));

            window.confirmBulkDelete = () => {
                const n = boxes.filter(b => b.checked).length;
                return n > 0 && confirm('Excluir ' + n + ' produto(s)?');
            };

            update();
        })();
    </script>
</x-app.layout>