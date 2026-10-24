@props(['title', 'message'])
<x-app.card class="p-4" style="border-color: color-mix(in srgb, var(--color-mv-danger) 33%, transparent); background: var(--color-mv-danger-bg)">
    <div class="flex items-start gap-3">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-mv-danger-solid text-white">
            <x-app.icon name="warning" :size="16" />
        </div>
        <div>
            <p class="font-semibold text-mv-danger">{{ $title }}</p>
            <p class="mt-0.5 text-[14px] text-mv-text-secondary">{{ $message }}</p>
        </div>
    </div>
</x-app.card>
