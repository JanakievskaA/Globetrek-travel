@props([
    'stats' => [],
    'section' => null,
])

@php
    $image = $section?->value('image') ?? 'assets/images/travel/mountains-clouds.jpg';
    $video = $section?->value('video_url') ?? 'https://www.youtube.com/embed/x7X9w_GIm1s?autoplay=1';
    $text = $section?->value('text') ?? 'Two minutes on where we go and why it is worth the early starts.';
@endphp

<div class="banner-section banner-2" style="background-image:url('{{ asset($image) }}')">
    <div class="container">
        <div class="content">
            @if ($video)
                <div class="tf-action wow animate__animated animate__zoomIn">
                    <a class="tf-btn" data-fancybox data-type="iframe" href="{{ $video }}" aria-label="Play showreel">
                        <i class="icon-play-filled icon"></i>
                    </a>
                </div>
            @endif
            <div class="box-title wow animate__animated animate__fadeInUp">
                <div class="title h1">{{ $section?->heading ?? 'Journey to discover amazing nature' }}</div>
                <div class="desc subtitle">
                    {{ number_format($stats['travellers'] ?? 0) }} travellers have booked with us across
                    {{ $stats['destinations'] ?? 0 }} destinations.<br>
                    {{ $text }}
                </div>
            </div>
        </div>
    </div>
</div>
