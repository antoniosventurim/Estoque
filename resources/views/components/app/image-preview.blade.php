@props(['src' => null, 'alt' => 'Imagem', 'class' => ''])
<div class="flex items-center justify-center overflow-hidden rounded-lg border border-mv-border bg-mv-surface2 {{ $class }}">
    @if ($src)
        <button
            type="button"
            data-image-zoom
            data-src="{{ $src }}"
            data-alt="{{ $alt }}"
            class="group relative flex h-full w-full cursor-zoom-in items-center justify-center"
            aria-label="Ampliar imagem"
        >
            <img src="{{ $src }}" alt="{{ $alt }}" class="h-full w-full object-cover">
            <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                <x-app.icon name="search" :size="22" class="text-white" />
            </span>
        </button>
    @else
        {{ $slot }}
    @endif
</div>