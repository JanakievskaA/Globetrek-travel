@props([
    'stats' => [],
    'section' => null,
])

@php
    $label = fn (string $field, string $fallback) => $section?->value($field) ?? $fallback;

    $items = [
        ['value' => $stats['tours'] ?? 0, 'label' => $label('tours_label', 'Curated tours'), 'suffix' => ''],
        ['value' => $stats['destinations'] ?? 0, 'label' => $label('destinations_label', 'Destinations'), 'suffix' => ''],
        ['value' => $stats['travellers'] ?? 0, 'label' => $label('travellers_label', 'Travellers hosted'), 'suffix' => '+'],
        ['value' => $stats['rating'] ?? 0, 'label' => $label('rating_label', 'Average rating'), 'suffix' => '/5'],
    ];
@endphp

<div class="gt-stats-bar">
    <div class="container">
        <div class="gt-stats-bar__grid">
            @foreach ($items as $item)
                <div class="gt-stat wow animate__animated animate__fadeInUp">
                    <div class="gt-stat__value">
                        {{ is_float($item['value']) ? number_format($item['value'], 1) : number_format($item['value']) }}{{ $item['suffix'] }}
                    </div>
                    <div class="gt-stat__label">{{ $item['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
