<x-app.layout>
    <x-app.page-header
        title="Entrada de Estoque"
        subtitle="Escaneie o produto, confirme e registre a entrada"
        icon="arrow-up"
    />

    <div class="space-y-4">

        {{-- Sucesso --}}
        @if ($success = session('success'))
            <x-app.card class="p-5" style="border-color: #34c45a55; background: #1a6b3522">
                <div class="mb-3 flex items-center gap-2.5">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-mv-success-solid text-white">
                        <x-app.icon name="check" :size="16" />
                    </div>
                    <span class="text-[15px] font-semibold text-mv-success">Entrada registrada com sucesso</span>
                </div>
                <div class="flex flex-wrap items-start gap-x-8 gap-y-3">
                    <div class="min-w-0">
                        <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Produto</p>
                        <p class="truncate text-[14px] font-medium text-mv-text">{{ $success['name'] }}</p>
                    </div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Quantidade adicionada</p><p class="text-[14px] font-medium text-mv-text">+{{ $success['qty'] }} un</p></div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque anterior</p><p class="text-[14px] font-medium text-mv-text">{{ $success['before'] }} un</p></div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Novo estoque</p><p class="text-[14px] font-medium text-mv-text">{{ $success['after'] }} un</p></div>
                    <div><p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque mínimo</p><p class="text-[14px] font-medium text-mv-text">{{ $success['min_stock'] ?? 0 }} un</p></div>
                </div>
            </x-app.card>
        @endif

        {{-- Erro (registro) --}}
        @if ($error = session('error'))
            <x-app.error-card title="Não foi possível registrar" :message="$error" />
        @endif

        {{-- Passo 1: Buscar produto --}}
        <x-app.card class="p-5">
            <x-app.product-name-search :route="route('entrada')" :options="$products" />
        </x-app.card>

        {{-- Passo 2: Confirmar produto --}}
        @if ($product)
            <x-app.card class="p-5">
                <div class="mb-4 flex items-center gap-3">
                    <x-app.image-preview
                        src="{{ $product->image ? asset('storage/'.$product->image) : null }}"
                        alt="{{ $product->name }}"
                        class="h-16 w-16 flex-shrink-0"
                    >
                        <x-app.icon name="package" :size="28" class="text-mv-accent-hover" />
                    </x-app.image-preview>
                    <div>
                        <p class="text-[15px] font-semibold text-mv-text leading-tight">{{ $product->name }}</p>
                        <p class="text-[14px] text-mv-text-muted leading-tight">Código: <span class="mono">{{ $product->barcode }}</span> @if($product->category) · {{ $product->category->name }} @endif</p>
                    </div>
                </div>

                <div class="mb-4 grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-mv-border bg-mv-surface2 p-3">
                        <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque atual</p>
                        <p id="entrada-stock" data-value="{{ $product->stock }}" class="text-xl font-bold text-mv-text">{{ $product->stock }} <span class="text-[14px] font-normal text-mv-text-muted">un</span></p>
                    </div>
                    <div class="rounded-lg border border-mv-border bg-mv-surface2 p-3">
                        <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque mínimo</p>
                        <p class="text-xl font-bold text-mv-warning">{{ $product->min_stock ?? 0 }} <span class="text-[14px] font-normal text-mv-text-muted">un</span></p>
                    </div>
                </div>

                <form method="POST" action="{{ route('entrada.register') }}" id="entrada-form">
                    @csrf
                    <input type="hidden" name="barcode" value="{{ $product->barcode }}">

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label for="quantity" class="mb-1.5 block text-[14px] font-medium text-mv-text-secondary">Quantidade</label>
                            <input id="quantity" name="quantity" type="number" min="1" value="1" class="w-full rounded-md border border-mv-border bg-mv-surface2 px-2.5 py-2 text-center text-[15px] font-semibold text-mv-text outline-none">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[14px] font-medium text-mv-text-secondary">Novo estoque</label>
                            <input id="entrada-new-stock" type="text" value="{{ $product->stock + 1 }} un" disabled class="w-full cursor-not-allowed rounded-md border border-mv-border bg-mv-surface2/60 px-2.5 py-2 text-center text-[15px] font-semibold text-mv-success">
                        </div>
                    </div>

                    <div class="mt-3">
                        <x-app.searchable-select
                            name="cost_center_id"
                            label="Centro de Custo *"
                            placeholder="Buscar curso/setor..."
                            :required="true"
                            :options="$costCenters->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'sub' => ucfirst($c->type)])->values()->all()"
                        />
                    </div>

                    <button type="submit" class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg bg-mv-accent py-3 text-sm font-semibold text-white hover:bg-mv-accent-hover">
                        <x-app.icon name="arrow-up" :size="16" /> Registrar Entrada
                    </button>
                </form>
            </x-app.card>
        @endif
    </div>

    <script>
        (function () {
            const qty = document.getElementById('quantity');
            const preview = document.getElementById('entrada-new-stock');
            const current = Number(document.getElementById('entrada-stock')?.dataset.value);
            if (!qty || !preview || Number.isNaN(current)) return;

            const render = () => {
                const q = Math.max(1, Number(qty.value) || 1);
                preview.value = (current + q) + ' un';
            };
            qty.addEventListener('input', render);
            render();
        })();
    </script>
</x-app.layout>