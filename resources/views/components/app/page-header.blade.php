@props(['title', 'subtitle' => null, 'icon' => null])
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="m-0 flex items-center gap-2 text-lg font-semibold text-mv-text">
            @if ($icon)<x-app.icon :name="$icon" :size="18" class="text-mv-text-secondary" />@endif
            {{ $title }}
        </h1>
        @if ($subtitle)<p class="mt-0.5 text-[14px] text-mv-text-secondary">{{ $subtitle }}</p>@endif
    </div>
    <div class="flex items-center gap-2">{{ $slot }}</div>
</div>