<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with(['tour'])
            ->search($request->string('q')->trim()->value() ?: null)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('rating'), fn ($q, $r) => $q->where('rating', $r))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'statuses' => ReviewStatus::options(),
            'totals' => [
                'all' => Review::count(),
                'pending' => Review::where('status', ReviewStatus::Pending)->count(),
                'approved' => Review::where('status', ReviewStatus::Approved)->count(),
                'average' => round((float) Review::approved()->avg('rating'), 2),
            ],
        ]);
    }

    public function show(Review $review): View
    {
        return view('admin.reviews.show', ['review' => $review->load(['tour', 'user'])]);
    }

    public function edit(Review $review): View
    {
        return view('admin.reviews.form', [
            'review' => $review->load('tour'),
            'statuses' => ReviewStatus::options(),
        ]);
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $review->update($request->validate([
            'author_name' => ['required', 'string', 'max:120'],
            'author_email' => ['nullable', 'email', 'max:180'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:4000'],
            'status' => ['required', Rule::enum(ReviewStatus::class)],
            'helpful_count' => ['required', 'integer', 'min:0'],
        ]) + ['is_featured' => $request->boolean('is_featured')]);

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Review updated.');
    }

    /** Approve / reject straight from the moderation queue. */
    public function updateStatus(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(ReviewStatus::class)]]);

        $review->update($data);

        return back()->with('success', "Review by {$review->author_name} marked as {$data['status']}.");
    }

    public function destroy(Review $review): RedirectResponse
    {
        $author = $review->author_name;
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', "Review by {$author} was deleted.");
    }
}
