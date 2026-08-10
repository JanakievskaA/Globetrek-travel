<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\PageSection;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function about(): View
    {
        $sections = PageSection::forPage('about');

        $stats = [
            'tours' => Tour::published()->count(),
            'destinations' => Destination::active()->count(),
            'reviews' => Review::approved()->count(),
            'rating' => round((float) Tour::published()->avg('rating_avg'), 1),
        ];

        return view('pages.about', [
            'sections' => $sections,
            'stats' => $stats + ['travellers' => (int) $sections['about_stats']->value('travellers')],
            'paragraphs' => $this->paragraphs($sections['about_intro']->value('body'), [
                ':tours' => number_format($stats['tours']),
                ':destinations' => number_format($stats['destinations']),
                ':reviews' => number_format($stats['reviews']),
                ':rating' => $stats['rating'],
            ]),
            'destinations' => Destination::active()->featured()
                ->withCount(['publishedTours as tours_count'])
                ->orderBy('sort_order')->take(6)->get(),
        ]);
    }

    public function contact(): View
    {
        $sections = PageSection::forPage('contact');

        return view('pages.contact', [
            'sections' => $sections,
            'paragraphs' => $this->paragraphs($sections['contact_intro']->value('body')),
        ]);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        // A production build would dispatch a mailable here; the demo simply
        // acknowledges the submission.
        return back()->with('success', PageSection::for('contact_form')->value('success_message'));
    }

    public function newsletter(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:180']]);

        return back()->with('success', 'You are on the list. Watch out for the next dispatch.');
    }

    /**
     * Split an editable body into paragraphs on blank lines, substituting any
     * live figures the copy refers to (":tours" and friends).
     */
    private function paragraphs(?string $body, array $replacements = []): array
    {
        $body = strtr((string) $body, $replacements);

        return collect(preg_split('/\R{2,}/', $body))
            ->map(fn (string $paragraph) => Str::squish($paragraph))
            ->filter()
            ->values()
            ->all();
    }
}
