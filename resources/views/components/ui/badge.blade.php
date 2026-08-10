@props([
    'tone' => 'muted',
    'label' => null,
])

<span {{ $attributes->merge(['class' => 'gt-badge gt-badge--'.$tone]) }}>{{ $label ?? $slot }}</span>
