<?php

namespace App\Http\Controllers;

use App\Enums\DurationBucket;
use App\Enums\TourSort;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Tour;
use App\Support\TourFilters;
use Database\Seeders\Data\TourCatalogue;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Request $request): View
    {
        $filters = TourFilters::fromRequest($request);

        $tours = Tour::query()
            ->published()
            ->withEffectivePrice()
            ->with(['destination', 'category', 'images'])
            ->filter($filters)
            ->paginate(9)
            ->withQueryString();

        $categories = Category::active()
            ->withCount(['publishedTours as tours_count'])
            ->orderBy('sort_order')
            ->get();

        $destinations = Destination::active()
            ->withCount(['publishedTours as tours_count'])
            ->orderBy('name')
            ->get();

        return view('pages.tours.index', [
            'tours' => $tours,
            'filters' => $filters,
            'categories' => $categories,
            'destinations' => $destinations,
            'durations' => DurationBucket::cases(),
            'amenities' => TourCatalogue::AMENITIES,
            'sortOptions' => TourSort::options(),
            'priceBounds' => [TourFilters::PRICE_FLOOR, TourFilters::PRICE_CEILING],
        ]);
    }

    public function show(Tour $tour): View
    {
        abort_unless($tour->status->value === 'published', 404);

        $tour->loadMissing(['destination', 'category', 'images', 'itineraries'])
            ->increment('views');

        $reviews = $tour->approvedReviews()->with('user')->paginate(5);

        return view('pages.tours.show', [
            'tour' => $tour,
            'reviews' => $reviews,
            'ratingBreakdown' => $this->ratingBreakdown($tour),
            'relatedTours' => Tour::published()
                ->with(['destination', 'category', 'images'])
                ->where('id', '!=', $tour->id)
                ->where(fn ($q) => $q
                    ->where('destination_id', $tour->destination_id)
                    ->orWhere('category_id', $tour->category_id))
                ->orderByDesc('rating_avg')
                ->take(6)
                ->get(),
        ]);
    }

    /** Star distribution for the review summary bars. */
    private function ratingBreakdown(Tour $tour): array
    {
        $counts = $tour->reviews()
            ->where('status', 'approved')
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $overall = max(1, $counts->sum());

        return collect(range(5, 1))
            ->mapWithKeys(fn (int $star) => [$star => [
                'count' => (int) ($counts[$star] ?? 0),
                'percent' => round((($counts[$star] ?? 0) / $overall) * 100),
            ]])
            ->all();
    }
}
