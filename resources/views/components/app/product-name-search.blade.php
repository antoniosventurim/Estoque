@props(['route', 'options', 'label' => 'Buscar pelo nome'])
<div class="w-full" data-product-name-search data-route="{{ $route }}">
    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-mv-text-secondary">{{ $label }}</label>
    <x-app.searchable-select
        name="product_pick"
        placeholder="Digite o nome do produto..."
        :options="$options"
    />
</div>