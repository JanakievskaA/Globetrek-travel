@props([
    'destination',
    'showCountry' => true,
])

@php
    $label = $showCountry ? $destination->full_name : $destination->name;
    $count = $destination->tours_count ?? $destination->publishedTours()->count();
    $url = route('destinations.show', $destination);
@endphp

<div {{ $attributes->merge(['class' => 'item hover-img']) }}>
    <div class="destination-post">
        {{-- alt is empty on purpose: the caption below states the same thing,
             so repeating it would double up for screen readers. --}}
        <a href="{{ $url }}" class="img-style" tabindex="-1" aria-hidden="true">
            <img src="{{ asset($destination->image) }}" alt="" loading="lazy">
        </a>
        <div class="content">
            <h4><a href="{{ $url }}" class="link">{{ $label }}</a></h4>
            <div class="total-tour subtitle">{{ $count }} {{ Str::plural('tour', $count) }}</div>
        </div>
    </div>
</div>
