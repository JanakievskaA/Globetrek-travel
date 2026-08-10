@props([
    'tour' => null,
    'amount' => null,
    'suffix' => null,
])

@php
    $current = $amount ?? ($tour?->effective_price ?? 0);
    $was = $tour?->is_on_sale ? (float) $tour->price : null;
@endphp

<div {{ $attributes->merge(['class' => 'price h5']) }}>
    @if ($was)
        <span class="gt-price-was">${{ number_format($was, 0) }}</span>
    @endif
    <span class="gt-price-now">${{ number_format($current, 0) }}</span>
    @if ($suffix)
        <span class="gt-price-suffix">{{ $suffix }}</span>
    @endif
</div>
