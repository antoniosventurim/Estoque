@props(['stock', 'minStock'])
@php
    $margin = (int) \App\Models\Setting::get('alerta_estoque_acima_minimo', 50);
    $warnThreshold = $minStock * (1 + $margin / 100);

    if ($stock == 0) {
        $cls = 'bg-mv-danger-bg text-mv-danger';
        $label = 'Zerado';
    } elseif ($stock <= $minStock) {
        $cls = 'bg-mv-danger-bg text-mv-danger';
        $label = 'Crítico';
    } elseif ($stock <= $warnThreshold) {
        $cls = 'bg-mv-warning-bg text-mv-warning';
        $label = 'Baixo';
    } else {
        $cls = 'bg-mv-success-bg text-mv-success';
        $label = 'Normal';
    }
@endphp
<span class="inline-block rounded px-1.5 py-0.5 text-[13px] font-medium {{ $cls }}">{{ $label }}</span>