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
                                <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Diferença (+/-)</p>
                                <input id="adjustment" type="number" name="adjustment" value="0"
                                    class="mt-1 w-full rounded-md border border-mv-border bg-mv-bg px-2 py-2 text-center text-lg font-semibold text-mv-text outline-none">
                                <p class="mt-1 text-[12px] text-mv-text-muted">+ sobra / - quebra</p>
                            </div>
                            <div class="rounded-lg border p-4" id="preview-box" style="background: #222; border-color: #2e2e2e55">
                                <p class="text-[13px] uppercase tracking-wider text-mv-text-muted">Novo estoque</p>
                                <p id="preview-val" class="mt-1 text-2xl font-bold text-mv-text-muted">{{ $selected->stock }}</p>
                            </div>
                        </div>
                        <script>
                            (() => {
                                const system = {{ $selected->stock }};
                                const input = document.getElementById('adjustment');
                                const val = document.getElementById('preview-val');
                                const box = document.getElementById('preview-box');
                                const paint = () => {
                                    const adj = parseInt(input.value) || 0;
                                    const preview = Math.max(0, system + adj);
                                    val.textContent = preview;
                                    const isDanger = adj < 0, isWarn = adj > 0, isOk = adj === 0;
                                    val.className = 'mt-1 text-2xl font-bold ' + (isDanger ? 'text-mv-danger' : isWarn ? 'text-mv-success' : 'text-mv-text-muted');
                                    box.style.background = isDanger ? '#8f0b2022' : isWarn ? '#1a6b3522' : '#222';
                                    box.style.borderColor = (isDanger ? '#e8334a' : isWarn ? '#34c45a' : '#2e2e2e') + '55';
                                };
                                input.addEventListener('input', paint);
                                paint();

                                const form = document.querySelector('form');
                                const submitBtn = document.getElementById('submit-btn');
                                if (form && submitBtn) {
                                    form.addEventListener('submit', function () {
                                        submitBtn.disabled = true;
                                        submitBtn.textContent = 'Salvando...';
                                    });
                                }
                            })();
                        </script>

                        <div class="mt-5">
                            <button type="submit" id="submit-btn" class="w-full rounded-lg bg-mv-accent px-6 py-3 text-sm font-semibold text-white hover:bg-mv-accent-hover sm:w-auto">
                                Registrar Ajuste
                            </button>
                        </div>
                    </form>
                </div>
            </x-app.card>
        @endif
    </div>
</x-app.layout>