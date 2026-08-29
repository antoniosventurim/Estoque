<x-app.layout>
    <x-app.page-header
        title="{{ $product ? 'Editar Produto' : 'Novo Produto' }}"
        subtitle="{{ $product ? $product->name : 'Cadastre um novo item no estoque' }}"
        icon="box"
    >
        <x-app.btn as="a" href="{{ route('produtos.index') }}" variant="ghost">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <x-app.card class="p-5">
        <form method="POST" action="{{ $product ? route('produtos.update', $product) : route('produtos.store') }}" enctype="multipart/form-data">
            @csrf
            @if ($product)@method('PUT')@endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-app.input label="Nome *" name="name" value="{{ old('name', $product->name ?? '') }}" placeholder="Nome do produto" required />
                </div>

                <x-app.searchable-select
                    label="Categoria"
                    name="category_id"
                    placeholder="Buscar categoria..."
                    :selected="old('category_id', $product->category_id ?? '')"
                    :selected-label="old('category_id', $product->category_id ?? '') ? ($categories->firstWhere('id', old('category_id', $product->category_id))->name ?? '') : ''"
                    :options="$categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'sub' => 'Categoria'])->values()->all()"
                />

                <x-app.searchable-select
                    label="Unidade de medida"
                    name="unit"
                    placeholder="Buscar unidade..."
                    :selected="old('unit', $product->unit ?? 'un')"
                    :selected-label="old('unit', $product->unit ?? 'un') ? ($units->firstWhere('abbreviation', old('unit', $product->unit ?? 'un'))?->name ?? '') : ''"
                    :options="$units->map(fn ($u) => ['id' => $u->abbreviation, 'name' => $u->name, 'sub' => $u->abbreviation])->values()->all()"
                />

                <x-app.input label="Estoque atual" name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" />
                <x-app.input label="Estoque mínimo" name="min_stock" type="number" min="0" value="{{ old('min_stock', $product->min_stock ?? 0) }}" />
            </div>

            {{-- Código de Barras + Imagem --}}
            <div class="mt-4 flex flex-col gap-4 sm:flex-row">
                @if ($product?->barcode)
                    <div class="flex-1">
                        <label class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Código de Barras</label>
                        <div class="inline-flex flex-col items-center gap-1.5 rounded-lg border border-mv-border bg-mv-surface2 p-4">
                            <img src="{{ route('produtos.barcode', $product) }}" alt="Código de barras" class="h-[50px]">
                            <span class="mono text-xs text-mv-text-secondary">{{ $product->barcode }}</span>
                        </div>
                        <input type="hidden" name="barcode" value="{{ $product->barcode }}">
                    </div>
                @endif

                <div class="flex-1">
                    <label class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Imagem do produto</label>
                    <div class="flex items-start gap-4">
                        <div class="flex h-24 w-24 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg border border-mv-border bg-mv-surface2">
                            @if ($product?->image)
                                <button type="button" data-image-zoom data-src="{{ asset('storage/'.$product->image) }}" data-alt="{{ $product->name }}" class="group relative flex h-full w-full cursor-zoom-in items-center justify-center" aria-label="Ampliar imagem">
                                    <img id="product-image-preview" src="{{ asset('storage/'.$product->image) }}" alt="Pré-visualização" class="h-full w-full object-cover">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                                        <x-app.icon name="search" :size="22" class="text-white" />
                                    </span>
                                </button>
                            @else
                                <span id="product-image-preview" class="flex h-full w-full items-center justify-center text-mv-text-muted">
                                    <x-app.icon name="box" :size="26" />
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2 pt-1">
                            <label class="inline-flex w-fit cursor-pointer items-center gap-2 rounded-md border border-mv-border bg-mv-surface2 px-3 py-2 text-[14px] text-mv-text-secondary transition-colors hover:border-mv-border-hover hover:text-mv-text">
                                <x-app.icon name="search" :size="14" />
                                Escolher imagem
                                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                            </label>
                            @if ($product?->image)
                                <button type="button" id="remove-image" class="w-fit rounded-md px-3 py-1.5 text-left text-[14px] text-mv-danger hover:bg-mv-danger-bg">Remover imagem</button>
                            @endif
                            <p class="text-[14px] text-mv-text-muted">JPG, PNG, WEBP ou GIF · máx. 2 MB</p>
                        </div>
                    </div>
                    <input type="hidden" name="remove_image" value="0" id="remove-image-flag">
                </div>
            </div>

            {{-- Status --}}
            <div class="mt-4">
                <x-bladewind::checkbox
                    name="is_active"
                    value="1"
                    label="Produto ativo"
                    color="blue"
                    add_clearing="false"
                    :checked="(bool) old('is_active', $product->is_active ?? true)"
                />
            </div>

            {{-- Botões --}}
            <div class="mt-5 flex gap-2">
                <x-app.btn type="submit" icon="check">{{ $product ? 'Salvar alterações' : 'Cadastrar Produto' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('produtos.index') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>

    <script>
        (function () {
            const input = document.getElementById('image');
            const holder = document.getElementById('product-image-preview');
            const removeBtn = document.getElementById('remove-image');
            const removeFlag = document.getElementById('remove-image-flag');
            if (!input || !holder) return;

            const placeholder = () => {
                holder.classList.remove('h-full', 'w-full', 'object-cover');
                holder.innerHTML = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>';
            };

            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) return;
                holder.classList.add('h-full', 'w-full', 'object-cover');
                holder.innerHTML = '<img src="' + URL.createObjectURL(file) + '" class="h-full w-full object-cover" alt="Pré-visualização">';
                if (removeFlag) removeFlag.value = '0';
                if (removeBtn) removeBtn.classList.add('hidden');
            });

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    if (input) input.value = '';
                    placeholder();
                    if (removeFlag) removeFlag.value = '1';
                    removeBtn.classList.add('hidden');
                });
            }
        })();
    </script>
</x-app.layout>
