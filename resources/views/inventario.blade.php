<x-app.layout>
    <x-app.page-header
        title="Inventário"
        subtitle="Conferência física e ajuste de estoque"
        icon="clipboard"
    />

    <div class="space-y-4">

        {{-- Mensagem --}}
        @if (session('success'))
            <x-app.card class="p-4" style="border-color: #34c45a55; background: #1a6b3522">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-mv-success-solid text-white">
                        <x-app.icon name="check" :size="16" />
                    </div>
                    <p class="font-semibold text-mv-success">{{ session('success') }}</p>
                </div>
            </x-app.card>
        @endif

        @if ($errors->any())
            <x-app.card class="p-4" style="border-color: #e8334a55; background: #8f0b2022">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-mv-danger-solid text-white">
                        <x-app.icon name="warning" :size="16" />
                    </div>
                    <p class="font-semibold text-mv-danger">{{ $errors->first() }}</p>
                </div>
            </x-app.card>
        @endif

        {{-- Seleção de produto --}}
        <x-app.card class="p-5">
            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-mv-text-secondary">Selecionar Produto</label>
            <form method="GET" action="{{ route('inventario') }}" class="flex items-end gap-2">
                <div class="flex-1">
                    <x-app.searchable-select
                        name="product_id"
                        placeholder="Buscar produto..."
                        :selected="$selected?->id ?? ''"
                        :selected-label="$selected ? $selected->name : ''"
                        :options="$products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name])->values()->all()"
                    />
                </div>
                <div class="relative flex-1">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-mv-text-muted">
                        <x-app.icon name="scan" :size="18" />
                    </div>
                    <input
                        name="barcode"
                        placeholder="Ou escaneie o código de barras..."
                        class="mono w-full rounded-md border border-mv-border bg-mv-surface2 py-2 pl-10 pr-3 text-[15px] text-mv-text outline-none placeholder:text-mv-text-muted"
                    >
                </div>
                <button type="submit" class="rounded-md bg-mv-accent px-4 py-2.5 text-[14px] font-semibold text-white hover:bg-mv-accent-hover">Buscar</button>
            </form>
        </x-app.card>

        {{-- Produto selecionado --}}
        @if ($selected)
            <x-app.card class="overflow-hidden">
                <div class="flex items-center gap-4 border-b border-mv-border px-5 py-3">
                    <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-mv-surface2 border border-mv-border">
                        @if ($selected->image)
                            <img src="{{ asset('storage/'.$selected->image) }}" alt="{{ $selected->name }}" class="h-full w-full object-cover">
                        @else
                            <x-app.icon name="box" :size="22" class="text-mv-accent-hover" />
                        @endif
                    </div>
                    <div>
                        <p class="text-[14px] font-semibold text-mv-text">Conferência: {{ $selected->name }}</p>
                        <p class="mono mt-0.5 text-xs text-mv-text-secondary">Código: {{ $selected->barcode }}</p>
                    </div>
                </div>

                <div class="p-5">
                    <form method="POST" action="{{ route('inventario.register') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $selected->id }}">

                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-lg border border-mv-border bg-mv-surface2 p-4">
                                <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Estoque no sistema</p>
                                <p class="mt-1 text-2xl font-bold text-mv-text">{{ $selected->stock }}</p>
                            </div>
                            <div class="rounded-lg border border-mv-border bg-mv-surface2 p-4">
                                <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Quantidade encontrada</p>
                                <input id="found-qty" type="number" name="found_qty" min="0" value="{{ old('found_qty', $selected->stock) }}"
                                    class="mt-1 w-full rounded-md border border-mv-border bg-mv-bg px-2 py-2 text-center text-lg font-semibold text-mv-text outline-none">
                            </div>
                            <div class="rounded-lg border p-4" id="diff-box" style="background: #222; border-color: #2e2e2e55">
                                <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Diferença</p>
                                <p id="diff-val" class="mt-1 text-2xl font-bold text-mv-text-muted">0</p>
                            </div>
                        </div>
                        <script>
                            (() => {
                                const system = {{ $selected->stock }};
                                const input = document.getElementById('found-qty');
                                const val = document.getElementById('diff-val');
                                const box = document.getElementById('diff-box');
                                const paint = () => {
                                    const d = (parseInt(input.value) || 0) - system;
                                    val.textContent = (d > 0 ? '+' : '') + d;
                                    const isDanger = d < 0, isWarn = d > 0, isOk = d === 0;
                                    val.className = 'mt-1 text-2xl font-bold ' + (isDanger ? 'text-mv-danger' : isWarn ? 'text-mv-warning' : 'text-mv-success');
                                    box.style.background = isDanger ? '#8f0b2022' : isWarn ? '#7a520022' : '#1a6b3522';
                                    box.style.borderColor = (isDanger ? '#e8334a' : isWarn ? '#e09b2a' : '#34c45a') + '55';
                                };
                                input.addEventListener('input', paint);
                                paint();
                            })();
                        </script>

                        <div class="mt-5">
                            <button type="submit" class="w-full rounded-lg bg-mv-accent px-6 py-3 text-sm font-semibold text-white hover:bg-mv-accent-hover sm:w-auto">
                                Registrar Ajuste
                            </button>
                        </div>
                    </form>
                </div>
            </x-app.card>
        @endif
    </div>
</x-app.layout>