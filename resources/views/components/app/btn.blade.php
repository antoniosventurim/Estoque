@props(['variant' => 'primary', 'size' => 'md', 'icon' => null, 'iconSize' => 14, 'as' => 'button'])
@php
    $styles = [
        'primary' => 'bg-mv-accent text-white hover:bg-mv-accent-hover',
        'secondary' => 'bg-mv-surface2 text-white border border-mv-border hover:border-mv-border-hover',
        'danger' => 'bg-mv-danger-solid text-white hover:opacity-90',
        'ghost' => 'bg-transparent text-mv-text-secondary border border-mv-border hover:text-mv-text hover:border-mv-border-hover',
    ];
    $pads = ['sm' => 'px-3 py-1.5 text-xs', 'md' => 'px-4 py-2 text-[14px]'];
    $classes = "inline-flex items-center gap-1.5 rounded-md font-medium transition-opacity disabled:opacity-40 disabled:cursor-not-allowed {$styles[$variant]} {$pads[$size]}";
@endphp
@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>@if ($icon)<x-app.icon :name="$icon" :size="$iconSize" />@endif{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>@if ($icon)<x-app.icon :name="$icon" :size="$iconSize" />@endif{{ $slot }}</button>
@endif