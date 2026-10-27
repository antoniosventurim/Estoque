@php
    $hasAddable = $nearProducts->isNotEmpty();
    $hasGenerate = $generateRows->isNotEmpty();
    $openAddModal = request()->boolean('adicionar');
@endphp

<x-app.layout>
    <x-app.page-header
        title="Lista de Compra"
        subtitle="{{ $rows->count() }} item(ns) na lista de compra"
        icon="clipboard"
    >
        <x-app.btn as="a" href="{{ route('solicitacoes.index') }}" icon="check">Acompanhar Solicitações</x-app.btn>
        <x-app.btn as="a" href="{{ route('lista-compra.export') }}" icon="download">Baixar Excel</x-app.btn>
        @can('lista-compra.create')
            <x-app.btn type="button" icon="plus" data-open-manual>Adicionar Itens</x-app.btn>
            @if ($hasAddable)
                <x-app.btn type="button" icon="plus" data-open-add>Adicionar Próximos</x-app.btn>
            @endif
        @endcan
        @if ($hasGenerate)
            <x-app.btn type="button" icon="paper" data-open-generate>Gerar Solicitação</x-app.btn>
        @endif
    </x-app.page-header>

    <x-app.flash />
    @if ($error = session('error'))
        <div class="mb-4 rounded-md border border-mv-danger-solid/50 bg-mv-danger-bg p-3 text-[14px] text-mv-danger">{{ $error }}</div>
    @endif
    <x-app.errors />

    @if ($rows->isEmpty())
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
                            <th class="whitespace-nowrap px-4 py-3 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $row)
                            @php
                                $p = $row['product'];
                                $manual = $row['manual'];
                            @endphp
                            <tr class="border-b border-mv-border hover:bg-black/[0.02] dark:hover:bg-white/[0.02]">
                                <td class="px-4 py-3 text-mv-text-secondary">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-mv-text">{{ $p->name }}</td>
                                <td class="px-4 py-3 text-mv-text-secondary">{{ $p->category->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $p->unitModel->name ?? $p->unit }}</td>
                                <td class="px-4 py-3 text-center font-semibold {{ $manual ? 'text-mv-warning' : 'text-mv-danger' }}">{{ $p->stock }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $p->min_stock }}</td>
                                <td class="px-4 py-3 text-center text-mv-text-secondary">{{ $p->max_stock }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $manual ? $manual->quantity : $p->quantity_to_order }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($manual)
                                        <span class="inline-block whitespace-nowrap rounded-full bg-mv-accent-soft px-3 py-1 text-[13px] font-semibold leading-snug text-mv-accent">Adicionado manualmente</span>
                                    @elseif ($pendingProductIds->contains($p->id))
                                        <span class="inline-block whitespace-nowrap rounded-full bg-mv-warning-bg px-3 py-1 text-[13px] font-semibold leading-snug text-mv-warning">Aguardando recebimento</span>
                                    @else
                                        <span class="inline-block whitespace-nowrap rounded-full border border-mv-border bg-mv-surface2 px-3 py-1 text-[13px] font-semibold leading-snug text-mv-text-secondary">Adicionado automaticamente</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($manual)
                                        @can('lista-compra.delete')
                                            <form method="POST" action="{{ route('lista-compra.destroyItem', $manual->id) }}" class="inline" data-confirm-title="Remover item da lista" data-confirm-name="{{ $p->name }}" data-confirm-message="O item sairá da lista de compra e voltará a contar apenas pelo cálculo automático de mínimo/máximo." data-confirm-accept="Remover item">
                                                @csrf
                                                @method('DELETE')
                                                <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                            </form>
                                        @endcan
                                    @else
                                        <span class="text-mv-text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-4 py-10 text-center text-[14px] text-mv-text-muted">Nenhum produto encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-mv-border px-4 py-3">
                <p class="text-xs text-mv-text-muted">
                    Total de itens para reposição: <span class="font-semibold text-mv-text">{{ $rows->count() }}</span>
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

    @can('lista-compra.create')
        @if ($hasAddable)
            {{-- Modal: adicionar próximos ao mínimo --}}
            <div id="add-modal" @if ($openAddModal) data-auto-open="1" @endif class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
                <div class="w-full max-w-2xl rounded-lg border border-mv-border bg-mv-surface shadow-2xl">
                    <div class="border-b border-mv-border px-5 py-4">
                        <h3 class="m-0 text-[15px] font-semibold text-mv-text">Adicionar Próximos ao Estoque Mínimo</h3>
                        <p class="mt-1 text-[14px] text-mv-text-secondary">
                            Produtos com estoque acima do mínimo, mas dentro da margem de alerta.
                            A coluna <strong>A Pedir</strong> já vem preenchida com a diferença até o estoque máximo — ajuste se precisar.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('lista-compra.adicionar') }}" data-add-form>
                        @csrf

                        <div class="max-h-[320px] overflow-y-auto">
                            <table class="w-full border-collapse text-[14px]">
                                <thead>
                                    <tr class="border-b border-mv-border bg-mv-surface2">
                                        <th class="px-4 py-2 text-left">
                                            <input type="checkbox" data-select-all checked class="h-4 w-4 cursor-pointer align-middle" title="Selecionar todos">
                                        </th>
                                        <th class="px-4 py-2 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Produto</th>
                                        <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Estoque</th>
                                        <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Mínimo</th>
                                        <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Máximo</th>
                                        <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">A Pedir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nearProducts as $p)
                                        <tr class="border-b border-mv-border">
                                            <td class="px-4 py-2.5">
                                                <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" data-row-check checked class="h-4 w-4 cursor-pointer align-middle">
                                            </td>
                                            <td class="px-4 py-2.5 text-mv-text">{{ $p->name }}</td>
                                            <td class="px-4 py-2.5 text-center font-semibold text-mv-warning">{{ $p->stock }}</td>
                                            <td class="px-4 py-2.5 text-center text-mv-text-secondary">{{ $p->min_stock }}</td>
                                            <td class="px-4 py-2.5 text-center text-mv-text-secondary">{{ $p->max_stock }}</td>
                                            <td class="px-4 py-2.5 text-center">
                                                <input type="number" name="quantities[{{ $p->id }}]" value="{{ max(1, $p->max_stock - $p->stock) }}" min="1" step="1" required
                                                    class="w-20 rounded-md border border-mv-border bg-mv-surface2 px-2 py-1.5 text-center text-[14px] font-semibold text-green-600 outline-none">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-mv-border px-5 py-3">
                            <button type="button" data-add-cancel class="rounded-md border border-mv-border bg-mv-surface2 px-4 py-2 text-[14px] font-medium text-mv-text-secondary transition-colors hover:border-mv-border-hover">Cancelar</button>
                            <button type="submit" class="rounded-md bg-mv-accent px-4 py-2 text-[14px] font-medium text-white transition-opacity hover:opacity-90">Adicionar itens à lista de compra</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endcan

    @can('lista-compra.create')
        {{-- Modal: adicionar itens manualmente (busca por nome) --}}
        <div id="manual-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-xl rounded-lg border border-mv-border bg-mv-surface shadow-2xl">
                <div class="border-b border-mv-border px-5 py-4">
                    <h3 class="m-0 text-[15px] font-semibold text-mv-text">Adicionar Itens à Lista de Compra</h3>
                    <p class="mt-1 text-[14px] text-mv-text-secondary">
                        Busque o produto pelo nome ou código, selecione e confirme. Itens adicionados assim entram como
                        <strong>adicionados manualmente</strong>.
                    </p>
                </div>

                <form method="POST" action="{{ route('lista-compra.adicionarManual') }}" data-manual-form>
                    @csrf

                    <div class="px-5 py-4">
                        <div class="relative">
                            <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="15" /></div>
                            <input type="text" data-manual-search placeholder="Buscar produto pelo nome ou código..."
                                autocomplete="off"
                                class="w-full rounded-md border border-mv-border bg-mv-surface2 py-2.5 pl-9 pr-3 text-[14px] text-mv-text outline-none transition-colors focus:border-mv-accent" />
                        </div>

                        <div data-manual-results class="mt-2 hidden max-h-44 overflow-y-auto rounded-md border border-mv-border"></div>

                        <p data-manual-empty class="mt-3 text-[13px] text-mv-text-muted">Nenhum item selecionado ainda.</p>
                        <div data-manual-selected class="mt-3 space-y-2"></div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-mv-border px-5 py-3">
                        <button type="button" data-manual-cancel class="rounded-md border border-mv-border bg-mv-surface2 px-4 py-2 text-[14px] font-medium text-mv-text-secondary transition-colors hover:border-mv-border-hover">Cancelar</button>
                        <button type="submit" data-manual-submit class="rounded-md bg-mv-accent px-4 py-2 text-[14px] font-medium text-white transition-opacity hover:opacity-90">Confirmar adição</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @if ($hasGenerate)
        {{-- Modal: gerar solicitação --}}
        <div id="generate-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-lg rounded-lg border border-mv-border bg-mv-surface shadow-2xl">
                <div class="border-b border-mv-border px-5 py-4">
                    <h3 class="m-0 text-[15px] font-semibold text-mv-text">Gerar Solicitação de Compra</h3>
                    <p class="mt-1 text-[14px] text-mv-text-secondary">
                        Os {{ $generateRows->count() }} item(ns) abaixo ainda não foram solicitados.
                        A quantidade padrão é a que falta para o estoque máximo (itens manuais usam a quantidade definida ao adicionar) —
                        ajuste se quiser pedir menos, ou zere para não incluir o item.
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
                                    <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Máximo</th>
                                    <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">A Pedir</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($generateRows as $row)
                                    @php $p = $row['product']; @endphp
                                    <tr class="border-b border-mv-border">
                                        <td class="px-4 py-2.5 text-mv-text">
                                            {{ $p->name }}
                                            @if ($row['manual'])
                                                <span class="ml-1.5 rounded-full bg-mv-accent-soft px-2 py-0.5 text-[12px] font-semibold text-mv-accent">manual</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-semibold {{ $row['manual'] ? 'text-mv-warning' : 'text-mv-danger' }}">{{ $p->stock }}</td>
                                        <td class="px-4 py-2.5 text-center text-mv-text-secondary">{{ $p->max_stock }}</td>
                                        <td class="px-4 py-2.5 text-center">
                                            <input type="number" name="quantities[{{ $p->id }}]" value="{{ $row['quantity'] }}" min="0" max="{{ $row['quantity'] }}" step="1"
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
    @endif

    <script>
        (function () {
            function setupModal(modal, openSelector, cancelSelector) {
                if (!modal) return null;

                function open() {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function close() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                document.querySelectorAll(openSelector).forEach(function (btn) {
                    btn.addEventListener('click', open);
                });

                modal.querySelectorAll(cancelSelector).forEach(function (btn) {
                    btn.addEventListener('click', close);
                });

                modal.addEventListener('click', function (e) {
                    if (e.target === modal) close();
                });

                return { open: open, close: close };
            }

            setupModal(document.getElementById('generate-modal'), '[data-open-generate]', '[data-generate-cancel]');

            // ── Modal de adição manual (busca por nome) ────────────────
            var manualModal = document.getElementById('manual-modal');
            var manual = setupModal(manualModal, '[data-open-manual]', '[data-manual-cancel]');

            if (manualModal) {
                var searchInput = manualModal.querySelector('[data-manual-search]');
                var resultsBox = manualModal.querySelector('[data-manual-results]');
                var selectedBox = manualModal.querySelector('[data-manual-selected]');
                var emptyMsg = manualModal.querySelector('[data-manual-empty]');
                var manualForm = manualModal.querySelector('[data-manual-form]');
                var selected = {};
                var searchTimer = null;

                function escapeHtml(value) {
                    return String(value).replace(/[&<>"']/g, function (c) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
                    });
                }

                function renderResults(items) {
                    resultsBox.innerHTML = '';

                    if (!items.length) {
                        resultsBox.classList.add('hidden');
                        return;
                    }

                    items.forEach(function (item) {
                        if (selected[item.id]) return;

                        var row = document.createElement('button');
                        row.type = 'button';
                        row.className = 'flex w-full items-center justify-between gap-3 border-b border-mv-border px-3 py-2.5 text-left text-[14px] text-mv-text transition-colors last:border-b-0 hover:bg-mv-surface2';
                        row.innerHTML =
                            '<span class="font-medium">' + escapeHtml(item.name) + '</span>' +
                            '<span class="shrink-0 text-[12px] text-mv-text-muted">Estoque: ' + escapeHtml(item.stock) + ' ' + escapeHtml(item.unit || '') + '</span>';
                        row.addEventListener('click', function () {
                            addSelected(item);
                            searchInput.value = '';
                            resultsBox.classList.add('hidden');
                            searchInput.focus();
                        });
                        resultsBox.appendChild(row);
                    });

                    resultsBox.classList.toggle('hidden', resultsBox.children.length === 0);
                }

                function addSelected(item) {
                    if (selected[item.id]) return;

                    selected[item.id] = { name: item.name, quantity: item.quantity, stock: item.stock, maxStock: item.max_stock };
                    renderSelected();
                }

                function renderSelected() {
                    selectedBox.innerHTML = '';
                    var ids = Object.keys(selected);

                    emptyMsg.classList.toggle('hidden', ids.length > 0);

                    ids.forEach(function (id) {
                        var item = selected[id];
                        var row = document.createElement('div');
                        row.className = 'flex items-center gap-2 rounded-md border border-mv-border bg-mv-surface2 px-3 py-2';
                        row.innerHTML =
                            '<span class="flex-1 text-[14px] font-medium text-mv-text">' + escapeHtml(item.name) +
                            '<span class="ml-2 text-[12px] font-normal text-mv-text-muted">Atual: ' + escapeHtml(item.stock) + ' | Máx: ' + escapeHtml(item.maxStock) + '</span></span>' +
                            '<input type="hidden" name="product_ids[]" value="' + escapeHtml(id) + '">' +
                            '<label class="text-[12px] text-mv-text-secondary">Qtd:</label>' +
                            '<input type="number" name="quantities[' + escapeHtml(id) + ']" min="1" step="1" value="' + escapeHtml(item.quantity) + '"' +
                            ' class="w-20 rounded-md border border-mv-border bg-mv-surface px-2 py-1 text-center text-[14px] font-semibold text-green-600 outline-none">' +
                            '<button type="button" class="text-mv-text-muted transition-colors hover:text-mv-danger" title="Remover">' +
                            '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
                        row.querySelector('button').addEventListener('click', function () {
                            delete selected[id];
                            renderSelected();
                        });
                        selectedBox.appendChild(row);
                    });
                }

                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimer);
                    var term = searchInput.value.trim();

                    if (term.length < 2) {
                        resultsBox.classList.add('hidden');
                        return;
                    }

                    searchTimer = setTimeout(function () {
                        fetch('{{ route('lista-compra.buscar') }}?q=' + encodeURIComponent(term), {
                            headers: { 'Accept': 'application/json' }
                        })
                            .then(function (response) { return response.json(); })
                            .then(renderResults)
                            .catch(function () { resultsBox.classList.add('hidden'); });
                    }, 250);
                });

                document.addEventListener('click', function (event) {
                    if (!manualModal.contains(event.target)) {
                        resultsBox.classList.add('hidden');
                    }
                });

                manualForm.addEventListener('submit', function (event) {
                    if (!Object.keys(selected).length) {
                        event.preventDefault();
                        return;
                    }

                    if (Object.keys(selected).length) {
                        // Impede envio de quantidades inválidas.
                        Array.prototype.forEach.call(manualForm.querySelectorAll('input[type="number"]'), function (input) {
                            if (parseInt(input.value, 10) < 1) input.value = 1;
                        });
                    }
                });

                if (manual) {
                    var manualOpen = manual.open;
                    manual.open = function () {
                        manualOpen();
                        setTimeout(function () { searchInput.focus(); }, 50);
                    };
                }
            }

            var addModal = document.getElementById('add-modal');
            var add = setupModal(addModal, '[data-open-add]', '[data-add-cancel]');

            if (addModal) {
                var form = addModal.querySelector('[data-add-form]');
                var selectAll = addModal.querySelector('[data-select-all]');
                var checks = function () {
                    return Array.prototype.slice.call(addModal.querySelectorAll('[data-row-check]'));
                };

                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        checks().forEach(function (c) { c.checked = selectAll.checked; });
                    });
                }

                checks().forEach(function (c) {
                    c.addEventListener('change', function () {
                        if (selectAll) selectAll.checked = checks().every(function (x) { return x.checked; });
                    });
                });

                if (form) {
                    form.addEventListener('submit', function () {
                        checks().forEach(function (c) {
                            var qty = c.closest('tr').querySelector('input[type="number"]');
                            if (qty) qty.disabled = !c.checked;
                        });
                    });
                }

                if (addModal.hasAttribute('data-auto-open') && add) {
                    add.open();
                }
            }
        })();
    </script>
</x-app.layout>
