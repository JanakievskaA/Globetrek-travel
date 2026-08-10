@props([
    'destinations' => collect(),
    'section' => null,
    'title' => null,
    'subtitle' => null,
])

@php
    // Other pages reuse this block with their own copy, so the explicit props
    // win over whatever the homepage section says.
    $title ??= $section?->heading ?? 'Top choice for your trip';
    $subtitle ??= $section?->subtitle ?? 'The destinations our travellers return to most.';
    $buttonLabel = $section?->value('button_label') ?? 'See all destinations';
@endphp

{{--
    A plain responsive grid rather than a Swiper. The template's carousel
    markup relies on Swiper's JS to size its slides, so leaving it
    uninitialised on wide screens collapsed every card to a sliver.
--}}
<div class="gt-section-tint">
    <div class="container">
        <x-ui.section-title :title="$title" :subtitle="$subtitle" />

        <div class="tf-destination gt-destination-grid">
            @foreach ($destinations as $destination)
                <x-destination.card :destination="$destination"
                    class="wow animate__animated animate__fadeInUp"
                    style="animation-delay: {{ $loop->index * 60 }}ms" />
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('destinations.index') }}" class="tf-btn primary hover-1">
                <span>{{ $buttonLabel }}</span>
            </a>
        </div>
    </div>
</div>
