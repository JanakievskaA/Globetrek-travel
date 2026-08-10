<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The wishlist lives in the browser (localStorage) so it works without an
 * account; this controller only resolves the saved ids into tour records.
 */
class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $ids = collect(explode(',', (string) $request->cookie('gt_wishlist')))
            ->filter(fn ($id) => ctype_digit(trim((string) $id)))
            ->map(fn ($id) => (int) trim($id))
            ->take(60);

        $tours = $ids->isEmpty()
            ? collect()
            : Tour::published()
                ->with(['destination', 'category', 'images'])
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn (Tour $tour) => $ids->search($tour->id))
                ->values();

        return view('pages.wishlist', ['tours' => $tours]);
    }

    /** Mirrors localStorage into a cookie so the server can render the page. */
    public function sync(Request $request): JsonResponse
    {
        $ids = collect($request->input('ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(60)
            ->implode(',');

        return response()
            ->json(['ok' => true])
            ->cookie('gt_wishlist', $ids, 60 * 24 * 90);
    }
}
