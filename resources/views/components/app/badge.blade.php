@props(['type'])
@php
    $map = [
        'in' => ['bg' => 'bg-mv-success-bg', 'text' => 'text-mv-success', 'label' => 'Entrada'],
        'out' => ['bg' => 'bg-mv-danger-bg', 'text' => 'text-mv-danger', 'label' => 'Saída'],
        'adjust' => ['bg' => 'bg-mv-warning-bg', 'text' => 'text-mv-warning', 'label' => 'Ajuste'],
    ];
    $c = $map[$type] ?? $map['adjust'];
@endphp
<span class="inline-block rounded px-1.5 py-0.5 text-[13px] font-medium {{ $c['bg'] }} {{ $c['text'] }}">{{ $c['label'] }}</span>