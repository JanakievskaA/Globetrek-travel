{{--
    About us; $sections carries one PageSection per block, already merged with
    the defaults in App\Support\PageSections. Hidden sections drop out here.
--}}
@php
    $hero = $sections['about_hero'];
    $intro = $sections['about_intro'];
    $principles = $sections['about_principles'];
    $whereWeGo = $sections['about_destinations'];
@endphp

<x-layouts.app title="About us"
    description="GlobeTrek runs small-group tours with local guides, honest pricing and fair cancellations.">

    @if ($hero->is_visible)
        <x-ui.page-title :title="$hero->heading" :image="$hero->value('image')" :breadcrumbs="['About' => null]" />
    @endif

    @if ($intro->is_visible)
        <div class="flat-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <img src="{{ asset($intro->value('image')) }}" alt="A traveller in the Alps"
                            class="w-full" style="border-radius:16px">
                    </div>
                    <div class="col-lg-6 mt-6 mt-lg-0">
                        <div class="gt-prose">
                            <div class="h2 mb-4">{{ $intro->heading }}</div>
                            @foreach ($paragraphs as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($sections['about_stats']->is_visible)
        <x-home.stats-bar :stats="$stats" />
    @endif

    @if ($principles->is_visible)
        <div class="flat-section">
            <div class="container">
                <x-ui.section-title :title="$principles->heading" :subtitle="$principles->subtitle" />

                <div class="gt-grid-3">
                    @foreach ($principles->rows('cards') as $card)
                        <div class="gt-card wow animate__animated animate__fadeInUp">
                            <div class="h5 mb-3">{{ $card['title'] ?? '' }}</div>
                            <p class="text-color">{{ $card['text'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($whereWeGo->is_visible && $destinations->isNotEmpty())
        <div class="flat-section pt-0">
            <div class="container">
                <x-ui.section-title :title="$whereWeGo->heading" :subtitle="$whereWeGo->subtitle" />
                <div class="tf-destination gt-grid-3">
                    @foreach ($destinations as $destination)
                        <x-destination.card :destination="$destination" />
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.app>
