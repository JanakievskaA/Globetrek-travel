<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value() ?: null;
        $continent = $request->string('continent')->value() ?: null;

        $destinations = Destination::active()
            ->withCount(['publishedTours as tours_count'])
            ->search($search)
            ->when($continent, fn ($q) => $q->where('continent', $continent))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        return view('pages.destinations.index', [
            'destinations' => $destinations,
            'search' => $search,
            'continent' => $continent,
            'continents' => Destination::active()->distinct()->orderBy('continent')->pluck('continent')->filter(),
            'featured' => Destination::active()->featured()
                ->withCount(['publishedTours as tours_count'])
                ->orderBy('sort_order')->take(6)->get(),
            'topDestinations' => Destination::active()
                ->withCount(['publishedTours as tours_count'])
                ->orderByDesc('tours_count')->take(6)->get(),
        ]);
    }

    public function show(Destination $destination): View
    {
        abort_unless($destination->is_active, 404);

        $tours = $destination->publishedTours()
            ->withEffectivePrice()
            ->with(['destination', 'category', 'images'])
            ->orderByDesc('is_featured')
            ->orderByDesc('rating_avg')
            ->paginate(9);

        return view('pages.destinations.show', [
            'destination' => $destination,
            'tours' => $tours,
            'categories' => Category::active()
                ->whereHas('tours', fn ($q) => $q
                    ->where('destination_id', $destination->id)
                    ->where('status', 'published'))
                ->withCount(['tours as tours_count' => fn ($q) => $q
                    ->where('destination_id', $destination->id)
                    ->where('status', 'published')])
                ->get(),
            'stats' => [
                'tours' => $destination->publishedTours()->count(),
                'rating' => round((float) $destination->publishedTours()->avg('rating_avg'), 1),
                'reviews' => (int) $destination->publishedTours()->sum('reviews_count'),
                'from' => (float) $destination->publishedTours()->min('price'),
            ],
            'nearby' => Destination::active()
                ->where('id', '!=', $destination->id)
                ->where('continent', $destination->continent)
                ->withCount(['publishedTours as tours_count'])
                ->take(4)->get(),
            'topRated' => Tour::published()
                ->with(['destination', 'category', 'images'])
                ->where('destination_id', $destination->id)
                ->orderByDesc('rating_avg')->take(6)->get(),
        ]);
    }
}
