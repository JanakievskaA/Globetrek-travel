<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $sections = PageSection::content();

        $categories = Category::active()->withCount(['publishedTours as tours_count'])
            ->orderBy('sort_order')->get();

        return view('pages.home', [
            'sections' => $sections,

            'heroSlides' => $this->heroSlides($sections['hero']),

            'categories' => $categories,

            // The homepage tab strip: "View all" plus the six busiest categories.
            'tabCategories' => $categories->sortByDesc('tours_count')->take(6)->values(),

            'featuredTours' => Tour::published()->featured()
                ->with(['destination', 'category', 'images'])
                ->orderByDesc('rating_avg')->take(8)->get(),

            'toursByCategory' => Tour::published()
                ->with(['destination', 'category', 'images'])
                ->orderByDesc('rating_avg')->take(40)->get()
                ->groupBy(fn (Tour $tour) => $tour->category->slug),

            'topDestinations' => Destination::active()
                ->withCount(['publishedTours as tours_count'])
                ->orderByDesc('tours_count')->take(8)->get(),

            'trendingTours' => Tour::published()
                ->with(['destination', 'category', 'images'])
                ->orderByDesc('bookings_count')->take(8)->get(),

            'spotlight' => $this->spotlight($sections['spotlight']),

            'testimonials' => Review::approved()->where('is_featured', true)
                ->with(['tour.destination', 'user'])
                ->latest()->take(6)->get(),

            'stats' => [
                'tours' => Tour::published()->count(),
                'destinations' => Destination::active()->count(),
                'travellers' => (int) $sections['stats_bar']->value('travellers'),
                'rating' => round((float) Tour::published()->avg('rating_avg'), 1),
            ],
        ]);
    }

    /**
     * Slides configured in the admin, resolved into a flat shape for the view.
     * A slide may point at a destination for its meta strip and button, and may
     * override any of the text or the photo. With none configured we fall back
     * to the featured destinations, which is how the page shipped.
     */
    private function heroSlides(PageSection $hero): Collection
    {
        $rows = collect($hero->rows('slides'));

        if ($rows->isEmpty()) {
            return $this->featuredDestinations()->map(fn (Destination $destination) => $this->slide($destination));
        }

        $destinations = $this->destinationsById($rows->pluck('destination_id')->filter());

        return $rows
            ->map(function (array $row) use ($destinations) {
                $destination = $destinations->get((int) ($row['destination_id'] ?? 0));

                return $this->slide($destination, $row);
            })
            // A slide with no photo and no destination has nothing to show.
            ->filter(fn (array $slide) => filled($slide['image']))
            ->values();
    }

    private function slide(?Destination $destination, array $overrides = []): array
    {
        return [
            'image' => $overrides['image'] ?? $destination?->hero_image ?? $destination?->image,
            'eyebrow' => $overrides['eyebrow'] ?? $destination?->continent,
            'title' => $overrides['title'] ?? $destination?->name,
            'summary' => $overrides['summary'] ?? $destination?->summary,
            'destination' => $destination,
        ];
    }

    private function featuredDestinations(): Collection
    {
        return Destination::active()->featured()
            ->withCount(['publishedTours as tours_count'])
            ->orderBy('sort_order')->take(3)->get();
    }

    private function destinationsById(Collection $ids): Collection
    {
        return Destination::query()
            ->whereIn('id', $ids)
            ->withCount(['publishedTours as tours_count'])
            ->get()
            ->keyBy('id');
    }

    /** The spotlight destination: the chosen one, or a featured one at random. */
    private function spotlight(PageSection $section): ?Destination
    {
        $chosen = $section->value('destination_id');

        $query = Destination::active()->withCount(['publishedTours as tours_count']);

        return $chosen
            ? (clone $query)->whereKey($chosen)->first() ?? $query->featured()->inRandomOrder()->first()
            : $query->featured()->inRandomOrder()->first();
    }
}
