@props(['label' => null, 'name' => null, 'type' => 'text', 'value' => '', 'placeholder' => null, 'required' => false, 'readonly' => false, 'mono' => false, 'autofocus' => false, 'step' => null, 'min' => null])
<div class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="mb-1.5 block text-xs font-medium text-mv-text-secondary">{{ $label }}</label>
    @endif
    <input
        id="{{ $name }}"
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $autofocus ? 'autofocus' : '' }}
        {{ $step !== null ? 'step="'.$step.'"' : '' }}
        {{ $min !== null ? 'min="'.$min.'"' : '' }}
        @class([
            'w-full rounded-md border border-mv-border bg-mv-surface2 px-2.5 py-2.5 text-[14px] outline-none transition-colors focus:border-mv-border',
            'text-mv-text' => ! $readonly,
            'text-mv-text-secondary cursor-not-allowed' => $readonly,
            'mono' => $mono,
        ])
    />
</div>