@props([
    'label',
    'value',
    'delta' => null,
    'hint' => null,
    'icon' => 'icon-Category',
    'tone' => 'brand',
])

<div class="adm-stat adm-stat--{{ $tone }}">
    <div class="adm-stat__icon"><i class="icon {{ $icon }}"></i></div>
    <div class="adm-stat__body">
        <div class="adm-stat__label">{{ $label }}</div>
        <div class="adm-stat__value">{{ $value }}</div>
        @if ($delta !== null)
            <div class="adm-stat__delta {{ $delta >= 0 ? 'is-up' : 'is-down' }}">
                {{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}% vs last month
            </div>
        @elseif ($hint)
            <div class="adm-stat__hint">{{ $hint }}</div>
        @endif
    </div>
</div>
