{{--
    Section content (headings, photos, cards) is edited in the admin under
    Homepage; $sections carries one HomeSection per block, already merged with
    the defaults in App\Support\PageSections. Hidden sections drop out here.
--}}
@php
    // Without the hero behind it the transparent header would sit on white.
    $showHero = $sections['hero']->is_visible && $heroSlides->isNotEmpty();
@endphp

<x-layouts.app :header="$showHero ? 'overlay' : 'solid'"
    title="Small-group tours worth taking"
    description="GlobeTrek runs small-group and tailor-made tours across {{ $stats['destinations'] }} destinations, with local guides, honest pricing and fair cancellations.">

    @if ($showHero)
        <x-slot:hero>
            <x-home.hero :slides="$heroSlides" :destinations="$topDestinations" :categories="$categories"
                :stats="$stats" />
        </x-slot:hero>
    @endif

    @if ($sections['stats_bar']->is_visible)
        <x-home.stats-bar :stats="$stats" :section="$sections['stats_bar']" />
    @endif

    @if ($sections['categories']->is_visible)
        <x-home.categories :categories="$categories" :section="$sections['categories']" />
    @endif

    @if ($sections['benefits']->is_visible)
        <x-home.benefits :stats="$stats" :section="$sections['benefits']" />
    @endif

    @if ($sections['spotlight']->is_visible)
        <x-home.spotlight :destination="$spotlight" :section="$sections['spotlight']" />
    @endif

    @if ($sections['tour_tabs']->is_visible)
        <x-home.tour-tabs :featured-tours="$featuredTours" :tours-by-category="$toursByCategory"
            :tab-categories="$tabCategories" :section="$sections['tour_tabs']" />
    @endif

    @if ($sections['video_banner']->is_visible)
        <x-home.video-banner :stats="$stats" :section="$sections['video_banner']" />
    @endif

    @if ($sections['trending']->is_visible)
        <x-home.trending :tours="$trendingTours" :section="$sections['trending']" />
    @endif

    @if ($sections['destinations']->is_visible)
        <x-home.destinations :destinations="$topDestinations" :section="$sections['destinations']" />
    @endif

    @if ($sections['testimonials']->is_visible)
        <x-home.testimonials :testimonials="$testimonials" :section="$sections['testimonials']" />
    @endif
</x-layouts.app>
