<x-app.layout>
    <x-app.page-header
        title="Saída de Estoque"
        subtitle="Busque os produtos, defina quantidades e registre a saída"
        icon="arrow-down"
    />

    <div class="space-y-4">

        {{-- Sucesso (saída em lote) --}}
        @if ($success = session('success_batch'))
            <x-app.card id="saida-batch-success-card" class="border border-green-500/40 bg-green-500/10 p-5">
                <div class="mb-3 flex items-center gap-2.5">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-green-500 text-white">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <span class="text-[15px] font-semibold text-green-400">{{ $success['count'] }} saída(s) registrada(s) com sucesso</span>
                    <span id="saida-batch-countdown" class="ml-auto text-[13px] text-mv-text-muted"></span>
                </div>
                <div class="mb-3 flex flex-wrap items-start gap-x-8 gap-y-3">
                    <div class="min-w-0">
                        <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Funcionário</p>
                        <p class="text-[14px] font-medium text-mv-text">{{ $success['employee'] }}</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-mv-border">
                                <th class="pb-2 pr-4 font-semibold text-mv-text-secondary">Produto</th>
                                <th class="pb-2 pr-4 text-center font-semibold text-mv-text-secondary">Qtd</th>
                                <th class="pb-2 pr-4 text-center font-semibold text-mv-text-secondary">Estoque Anterior</th>
                                <th class="pb-2 text-center font-semibold text-mv-text-secondary">Estoque Atual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($success['items'] as $item)
                                <tr class="border-b border-mv-border/50">
                                    <td class="py-2.5 pr-4 font-medium text-mv-text">{{ $item['name'] }}</td>
                                    <td class="py-2.5 pr-4 text-center font-semibold text-mv-text">{{ $item['qty'] }} un</td>
                                    <td class="py-2.5 pr-4 text-center text-mv-text-muted">{{ $item['before'] }} un</td>
                                    <td class="py-2.5 text-center font-semibold text-green-400">{{ $item['after'] }} un</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-app.card>
        @endif

        {{-- Sucesso (saída individual) --}}
        @if ($success = session('success'))
            <x-app.card id="saida-success-card" class="border border-green-500/40 bg-green-500/10 p-5">
                <div class="mb-3 flex items-center gap-2.5">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-green-500 text-white">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <span class="text-[15px] font-semibold text-green-400">Saída registrada com sucesso</span>
                    <span id="saida-success-countdown" class="ml-auto text-[13px] text-mv-text-muted"></span>
                </div>
                <div class="flex flex-wrap items-start gap-x-8 gap-y-3">
                    <div class="min-w-0">
                        <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Produto</p>
                        <p class="truncate text-[14px] font-medium text-mv-text">{{ $success['name'] }}</p>
                    </div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Quantidade retirada</p><p class="text-[14px] font-medium text-mv-text">-{{ $success['qty'] }} un</p></div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque anterior</p><p class="text-[14px] font-medium text-mv-text">{{ $success['before'] }} un</p></div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Novo estoque</p><p class="text-[14px] font-medium text-mv-text">{{ $success['after'] }} un</p></div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque mínimo</p><p class="text-[14px] font-medium text-mv-text">{{ $success['min_stock'] ?? 0 }} un</p></div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Retirado por</p><p class="text-[14px] font-medium text-mv-text">{{ $success['employee'] ?? '' }}</p></div>
                </div>
            </x-app.card>
        @endif

        {{-- Erro --}}
        @if ($error = session('error'))
            <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4">
                <p class="text-[14px] font-medium text-red-400">{{ $error }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-500/40 bg-red-500/10 p-4">
                <ul class="list-disc pl-4 text-[14px] text-red-400">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Buscar produto --}}
        <x-app.card class="p-5">
            <x-app.product-name-search :route="''" :options="$products" />
        </x-app.card>

        {{-- Produto encontrado --}}
        <x-app.card id="saida-product-card" class="hidden p-5">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-lg bg-mv-surface2">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" class="text-mv-accent-hover">
                        <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p id="saida-product-name" class="text-[15px] font-semibold text-mv-text leading-tight"></p>
                    <p id="saida-product-info" class="text-[14px] text-mv-text-muted leading-tight"></p>
                </div>
                <button type="button" id="saida-close-product" class="flex h-8 w-8 items-center justify-center rounded-md text-mv-text-muted hover:bg-mv-surface2 hover:text-mv-text">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-3">
                <div class="rounded-lg border border-mv-border bg-mv-surface2 p-3">
                    <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque atual</p>
                    <p id="saida-product-stock" class="text-xl font-bold text-mv-text"></p>
                </div>
                <div class="rounded-lg border border-mv-border bg-mv-surface2 p-3">
                    <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque mínimo</p>
                    <p id="saida-product-min" class="text-xl font-bold text-mv-warning"></p>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-[14px] font-medium text-mv-text-secondary">Quantidade a retirar</label>
                    <input id="saida-product-qty" type="number" min="1" value="1" class="w-full rounded-md border border-mv-border bg-mv-surface2 px-2.5 py-2 text-center text-[15px] font-semibold text-mv-text outline-none">
                </div>
                <div class="flex items-end">
                    <button type="button" id="saida-add-btn" class="flex w-full items-center justify-center gap-2 rounded-lg bg-mv-accent py-2.5 text-sm font-semibold text-white hover:bg-mv-accent-hover">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Adicionar à lista
                    </button>
                </div>
            </div>
        </x-app.card>

        {{-- Lista de itens --}}
        <x-app.card id="saida-list-card" class="hidden p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-[14px] font-semibold text-mv-text">Itens para retirada</h3>
                <span id="saida-list-count" class="rounded-full bg-mv-surface2 px-2.5 py-0.5 text-[12px] font-medium text-mv-text-muted">0 itens</span>
            </div>

            <div id="saida-list" class="space-y-2"></div>

            <div class="mt-5 border-t border-mv-border pt-5">
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-app.searchable-select
                        name="cost_center_id"
                        label="Centro de Custo *"
                        placeholder="Buscar centro de custo..."
                        :required="true"
                        :options="$costCenters->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'sub' => ucfirst($c->type)])->values()->all()"
                    />

                    <x-app.searchable-select
                        name="employee_id"
                        label="Funcionário Responsável *"
                        placeholder="Buscar funcionário..."
                        :required="true"
                        :options="$employees->map(fn ($e) => ['id' => $e->id, 'name' => $e->name, 'sub' => ''])->values()->all()"
                    />
                </div>
            </div>

            <button type="button" id="saida-submit-btn" class="mt-5 flex w-full items-center justify-center gap-2.5 rounded-lg bg-mv-danger py-3.5 text-[15px] font-semibold text-white shadow-sm hover:opacity-90">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                Registrar Saídas
            </button>
        </x-app.card>
    </div>

    <form id="saida-batch-form" method="POST" action="{{ route('saida.batch') }}" class="hidden">
        @csrf
        <input type="hidden" name="items" id="saida-form-items">
        <input type="hidden" name="cost_center_id" id="saida-form-cc">
        <input type="hidden" name="employee_id" id="saida-form-emp">
    </form>

    <script>
    (function () {
        const items = [];
        const productsData = @json($products);

        const ICON_TRASH = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>';

        const productCard = document.getElementById('saida-product-card');
        const listCard = document.getElementById('saida-list-card');
        const listEl = document.getElementById('saida-list');
        const listCount = document.getElementById('saida-list-count');
        const submitBtn = document.getElementById('saida-submit-btn');

        let selectedProduct = null;

        // Interceptar seleção do product-name-search (preload)
        const searchBox = document.querySelector('[data-product-name-search]');
        if (searchBox) {
            searchBox.addEventListener('searchable:select', function (e) {
                const barcode = e.detail?.id;
                if (!barcode) return;

                const productData = productsData.find(p => p.id === barcode);
                if (!productData) return;

                selectedProduct = {
                    barcode: productData.id,
                    name: productData.name,
                    stock: productData.stock,
                    min_stock: productData.min_stock,
                    unit: productData.unit,
                    info: productData.sub || '',
                };

                showProductCard();
            });
        }

        function showProductCard() {
            if (!selectedProduct) return;
            document.getElementById('saida-product-name').textContent = selectedProduct.name;
            document.getElementById('saida-product-info').textContent = `Cod: ${selectedProduct.barcode} · ${selectedProduct.info}`;
            document.getElementById('saida-product-stock').textContent = `${selectedProduct.stock} ${selectedProduct.unit}`;
            document.getElementById('saida-product-min').textContent = `${selectedProduct.min_stock} ${selectedProduct.unit}`;
            document.getElementById('saida-product-qty').max = selectedProduct.stock;
            document.getElementById('saida-product-qty').value = 1;
            productCard.classList.remove('hidden');
        }

        document.getElementById('saida-close-product').addEventListener('click', function () {
            productCard.classList.add('hidden');
            selectedProduct = null;
        });

        // Adicionar à lista
        document.getElementById('saida-add-btn').addEventListener('click', function () {
            if (!selectedProduct) return;
            const qty = Number(document.getElementById('saida-product-qty').value) || 1;
            if (qty < 1) return;
            if (qty > selectedProduct.stock) { alert('Quantidade maior que o estoque disponível.'); return; }
            const existing = items.find(i => i.barcode === selectedProduct.barcode);
            if (existing) {
                existing.quantity = Math.min(existing.quantity + qty, selectedProduct.stock);
            } else {
                items.push({ barcode: selectedProduct.barcode, name: selectedProduct.name, stock: selectedProduct.stock, unit: selectedProduct.unit, quantity: qty });
            }
            renderList();
            productCard.classList.add('hidden');
            selectedProduct = null;
        });

        // Renderizar lista
        function renderList() {
            if (!items.length) { listCard.classList.add('hidden'); return; }
            listCard.classList.remove('hidden');
            listCount.textContent = `${items.length} item(s)`;
            listEl.innerHTML = items.map((item, idx) => `
                <div class="flex items-center gap-3 rounded-lg border border-mv-border bg-mv-surface2 p-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-[14px] font-medium text-mv-text truncate">${item.name}</p>
                        <p class="text-[12px] text-mv-text-muted">Cod: ${item.barcode} · Estoque: ${item.stock} ${item.unit}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" min="1" max="${item.stock}" value="${item.quantity}"
                               class="w-16 rounded border border-mv-border bg-mv-surface px-2 py-1.5 text-center text-[14px] font-semibold text-mv-text outline-none"
                               data-idx="${idx}" data-action="qty">
                        <span class="min-w-[28px] text-center text-[12px] text-mv-text-muted">${item.unit}</span>
                        <button type="button" data-idx="${idx}" data-action="remove"
                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg border border-red-500/30 bg-red-500/10 text-red-400 transition-colors hover:bg-red-500/20 hover:text-red-300"
                                title="Remover item">
                            ${ICON_TRASH}
                        </button>
                    </div>
                </div>
            `).join('');
        }

        listEl.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-action="remove"]');
            if (btn) { items.splice(Number(btn.dataset.idx), 1); renderList(); }
        });

        listEl.addEventListener('input', function (e) {
            const input = e.target.closest('[data-action="qty"]');
            if (input) {
                const idx = Number(input.dataset.idx);
                items[idx].quantity = Math.max(1, Math.min(Number(input.value) || 1, items[idx].stock));
            }
        });

        // Capturar valores dos searchable-selects para o form hidden
        function bindSearchableSelect(selectName, hiddenId) {
            document.addEventListener('searchable:select', function (e) {
                const target = e.target.closest(`[data-searchable-select]`);
                const hidden = target?.querySelector(`input[type="hidden"][name="${selectName}"]`);
                if (hidden) {
                    document.getElementById(hiddenId).value = hidden.value;
                }
            });
            document.addEventListener('click', function () {
                const target = document.querySelector(`[data-searchable-select] input[type="hidden"][name="${selectName}"]`);
                if (target) {
                    document.getElementById(hiddenId).value = target.value;
                }
            });
        }

        bindSearchableSelect('cost_center_id', 'saida-form-cc');
        bindSearchableSelect('employee_id', 'saida-form-emp');

        // Enviar formulário
        submitBtn.addEventListener('click', function () {
            if (!items.length) { alert('Adicione pelo menos um item à lista.'); return; }

            const ccHidden = document.querySelector('[name="cost_center_id"]');
            const empHidden = document.querySelector('[name="employee_id"]');
            const ccVal = ccHidden ? ccHidden.value : '';
            const empVal = empHidden ? empHidden.value : '';

            if (!ccVal) { alert('Selecione o centro de custo.'); return; }
            if (!empVal) { alert('Selecione o funcionário responsável.'); return; }

            document.getElementById('saida-form-items').value = JSON.stringify(items.map(i => ({ barcode: i.barcode, quantity: i.quantity })));
            document.getElementById('saida-form-cc').value = ccVal;
            document.getElementById('saida-form-emp').value = empVal;
            document.getElementById('saida-batch-form').submit();
        });

        // Countdown da mensagem de sucesso (20s)
        const successCard = document.getElementById('saida-success-card');
        const countdownEl = document.getElementById('saida-success-countdown');
        if (successCard && countdownEl) {
            let remaining = 20;
            countdownEl.textContent = `Removendo notificação em ${remaining}s`;
            const interval = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(interval);
                    successCard.style.transition = 'opacity 0.3s';
                    successCard.style.opacity = '0';
                    setTimeout(() => successCard.remove(), 300);
                } else {
                    countdownEl.textContent = `Removendo notificação em ${remaining}s`;
                }
            }, 1000);
        }

        // Countdown da mensagem de sucesso em lote (20s)
        const batchSuccessCard = document.getElementById('saida-batch-success-card');
        const batchCountdownEl = document.getElementById('saida-batch-countdown');
        if (batchSuccessCard && batchCountdownEl) {
            let remaining = 20;
            batchCountdownEl.textContent = `Removendo notificação em ${remaining}s`;
            const interval = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(interval);
                    batchSuccessCard.style.transition = 'opacity 0.3s';
                    batchSuccessCard.style.opacity = '0';
                    setTimeout(() => batchSuccessCard.remove(), 300);
                } else {
                    batchCountdownEl.textContent = `Removendo notificação em ${remaining}s`;
                }
            }, 1000);
        }
    })();
    </script>
</x-app.layout>
