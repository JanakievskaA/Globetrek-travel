<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TourStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TourRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function index(Request $request): View
    {
        $tours = Tour::query()
            ->with(['destination', 'category'])
            ->search($request->string('q')->trim()->value() ?: null)
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->input('destination'), fn ($q, $id) => $q->where('destination_id', $id))
            ->when($request->input('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->input('featured') !== null && $request->input('featured') !== '',
                fn ($q) => $q->where('is_featured', $request->boolean('featured')))
            ->orderBy(
                in_array($request->input('sort'), ['title', 'price', 'rating_avg', 'created_at'], true)
                    ? $request->input('sort') : 'created_at',
                $request->input('dir') === 'asc' ? 'asc' : 'desc'
            )
            ->paginate(12)
            ->withQueryString();

        return view('admin.tours.index', [
            'tours' => $tours,
            'destinations' => Destination::orderBy('name')->pluck('name', 'id'),
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'statuses' => TourStatus::options(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tours.form', [
            'tour' => new Tour(['status' => TourStatus::Published, 'group_size' => 12, 'difficulty' => 'easy']),
            ...$this->formData(),
        ]);
    }

    public function store(TourRequest $request): RedirectResponse
    {
        $tour = Tour::create($request->payload());
        $this->syncImages($tour, $request->input('images', []));

        return redirect()
            ->route('admin.tours.edit', $tour)
            ->with('success', "“{$tour->title}” has been created.");
    }

    public function edit(Tour $tour): View
    {
        return view('admin.tours.form', [
            'tour' => $tour->load(['images', 'itineraries']),
            ...$this->formData(),
        ]);
    }

    public function update(TourRequest $request, Tour $tour): RedirectResponse
    {
        $tour->update($request->payload());
        $this->syncImages($tour, $request->input('images', []));

        return redirect()
            ->route('admin.tours.edit', $tour)
            ->with('success', 'Tour updated.');
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        $title = $tour->title;
        $tour->delete();

        return redirect()
            ->route('admin.tours.index')
            ->with('success', "“{$title}” was deleted.");
    }

    /**
     * The gallery repeater posts the whole list every time, so the simplest
     * correct thing is to rewrite it: rows keep the order they were dragged
     * into, and rows left without a photo are dropped.
     */
    private function syncImages(Tour $tour, array $rows): void
    {
        $keep = collect($rows)
            ->filter(fn ($row) => filled($row['path'] ?? null))
            ->values()
            ->map(fn ($row, $index) => [
                'path' => $row['path'],
                'alt' => $row['alt'] ?? null,
                'sort_order' => $index,
            ]);

        $tour->images()->delete();

        if ($keep->isNotEmpty()) {
            $tour->images()->createMany($keep->all());
        }
    }

    private function formData(): array
    {
        return [
            'destinations' => Destination::orderBy('name')->pluck('name', 'id'),
            'categories' => Category::orderBy('name')->pluck('name', 'id'),
            'statuses' => TourStatus::options(),
            'difficulties' => [
                'easy' => 'Easy',
                'moderate' => 'Moderate',
                'challenging' => 'Challenging',
                'extreme' => 'Extreme',
            ],
        ];
    }
}
