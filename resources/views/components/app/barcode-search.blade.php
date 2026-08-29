@props(['route', 'formId'])
<label for="barcode" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-mv-text-secondary">Código de Barras</label>
<form method="GET" action="{{ $route }}" id="{{ $formId }}">
    <div class="flex items-center gap-2">
        <div class="relative flex-1">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-mv-text-muted">
                <x-app.icon name="scan" :size="18" />
            </div>
            <input
                id="barcode" name="barcode" value="{{ old('barcode', request('barcode')) }}" autofocus
                placeholder="Escaneie ou digite o código..."
                class="mono w-full rounded-lg border-2 border-mv-border bg-mv-surface2 py-3 pl-11 pr-3 text-[15px] text-mv-text outline-none placeholder:text-mv-text-muted"
            >
        </div>
        <button type="submit" class="flex items-center gap-2 rounded-lg bg-mv-accent px-5 py-3 text-sm font-semibold text-white hover:bg-mv-accent-hover">
            <x-app.icon name="search" :size="16" /> Buscar
        </button>
    </div>
</form>

@if ($notFound ?? false)
    <div class="mt-4 flex items-center gap-2.5 rounded-md px-3 py-2.5 text-[14px] text-mv-danger" style="background:#8f0b2022; border:1px solid #e8334a55">
        <x-app.icon name="warning" :size="16" />
        Produto não encontrado para o código informado.
    </div>
@endif
