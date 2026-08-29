@props(['as' => 'div'])
<{{ $as }} {{ $attributes->merge(['class' => 'rounded-lg border border-mv-border bg-mv-surface']) }}>
    {{ $slot }}
</{{ $as }}>