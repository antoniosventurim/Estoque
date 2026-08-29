@props(['name' => null, 'label' => null, 'placeholder' => null, 'required' => false, 'options' => [], 'selected' => '', 'selectedLabel' => ''])
<div class="relative w-full" data-searchable-select>
    @if ($label)
        <label for="{{ $name }}-input" class="mb-1.5 block text-xs font-medium text-mv-text-secondary">{{ $label }}</label>
    @endif
    <div class="relative">
        <div class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted">
            <x-app.icon name="search" :size="14" />
        </div>
        <input
            type="text"
            id="{{ $name }}-input"
            aria-label="{{ $label ?? 'Buscar' }}"
            @if ($required) required @endif
            autocomplete="off"
            @if ($selected !== '') readonly data-has-value="1" @endif
            value="{{ $selectedLabel }}"
            placeholder="{{ $placeholder ?? 'Buscar...' }}"
            class="w-full rounded-md border border-mv-border bg-mv-surface2 py-2 pl-8 pr-9 text-[14px] text-mv-text outline-none placeholder:text-mv-text-muted transition-colors focus:border-mv-border"
        >
        <button type="button" data-clear class="absolute right-2 top-1/2 hidden -translate-y-1/2 text-mv-text-muted hover:text-mv-text" title="Limpar">
            <x-app.icon name="x" :size="14" />
        </button>
        <input type="hidden" name="{{ $name }}" value="{{ $selected }}">
    </div>
    <div class="absolute left-0 right-0 top-full z-50 mt-1 hidden max-h-56 overflow-y-auto rounded-md border border-mv-border bg-mv-surface shadow-lg" data-options>
        @foreach ($options as $o)
            <button
                type="button"
                class="w-full border-b border-mv-border/50 px-3 py-2 text-left text-[14px] text-mv-text last:border-0 hover:bg-white/5"
                data-value="{{ $o['id'] }}"
                data-display="{{ $o['name'] }}"
                data-search="{{ Str::lower($o['name'].' '.($o['sub'] ?? '')) }}"
            >
                <span class="font-medium">{{ $o['name'] }}</span>
                <span class="ml-1.5 text-[13px] text-mv-text-muted">{{ $o['sub'] ?? '' }}</span>
            </button>
        @endforeach
        <p class="hidden px-3 py-3 text-[14px] text-mv-text-muted" data-empty>Nenhum resultado para "<span data-empty-term></span>".</p>
    </div>
</div>
