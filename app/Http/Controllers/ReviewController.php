<?php

namespace App\Http\Controllers;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Tour $tour): RedirectResponse
    {
        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:120'],
            'author_email' => ['required', 'email', 'max:180'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        Review::create([
            ...$data,
            'tour_id' => $tour->id,
            'user_id' => $request->user()?->id,
            // Submitted reviews wait for a moderator in the admin panel.
            'status' => ReviewStatus::Pending,
        ]);

        return back()
            ->with('success', 'Thanks — your review has been submitted and will appear once approved.')
            ->withFragment('reviews');
    }
}
