@props([
    'value' => 0,
    'count' => null,
    'showScore' => true,
])

@php
    $value = (float) $value;
    $filled = (int) round($value);
@endphp

<div {{ $attributes->merge(['class' => 'rating']) }}>
    <ul class="list-star" role="img" aria-label="{{ number_format($value, 1) }} out of 5 stars">
        @for ($i = 1; $i <= 5; $i++)
            <li class="icon icon-star {{ $i <= $filled ? '' : 'gt-star-empty' }}"></li>
        @endfor
    </ul>
    @if ($showScore)
        <div class="rate">
            <div class="total-rate">{{ $value > 0 ? number_format($value, 1) : '—' }}</div>
            @if ($count !== null)
                <div class="review">({{ number_format($count) }})</div>
            @endif
        </div>
    @endif
</div>
