<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Review bodies are assembled from real-sounding fragments so that no two
     * tours read identically, without needing a hand-written review per tour.
     */
    private const POSITIVE_OPENERS = [
        'Genuinely one of the best days of the whole trip.',
        'Exceeded what we expected, and we had high expectations.',
        'Booked this on a whim and it turned out to be the highlight of the holiday.',
        'Worth every penny and then some.',
        'Third time booking with GlobeTrek and the standard has not slipped once.',
        'If you are on the fence about this one, book it.',
    ];

    private const POSITIVE_BODIES = [
        'Our guide clearly knew the area inside out and adjusted the pace when a couple of people in the group struggled with the heat.',
        'The group was small enough that everyone got to ask questions, which made a real difference compared to the big coach tours we saw.',
        'Timing was the clever part — we were at the main sites before everyone else arrived, and it completely changed the experience.',
        'Everything ran to schedule, pickup was exactly when they said, and there were no surprise costs at any point.',
        'The food included was much better than the usual tour lunch. Actual local places, not somewhere set up for tourists.',
        'They handled a last-minute dietary request without any fuss and checked in with us twice during the day.',
    ];

    private const POSITIVE_CLOSERS = [
        'Would book again without hesitating.',
        'Already recommended it to two friends.',
        'Bring decent shoes and a hat and you will have a great time.',
        'Do the early start — it is worth losing the lie-in.',
        'Highly recommended if you want something more than the standard tourist route.',
    ];

    private const MIXED_REVIEWS = [
        'Really enjoyed the day overall, though the schedule was tighter than I expected and I would have liked longer at the second stop. Guide was excellent and the transport was comfortable. Just go in knowing it moves at a decent pace.',
        'Good value and a knowledgeable guide. My only real gripe is that the group was at the upper end of the stated size, which made it harder to hear at the busier sites. Everything else was as described.',
        'The main attraction absolutely lived up to it. The lunch stop was fairly average and felt a bit rushed, but that is a small complaint against an otherwise very good day out.',
        'Booking and communication beforehand were faultless. On the day the weather turned and a couple of the stops were cut short, which was nobody’s fault, but I would build in a spare day if you can.',
    ];

    private const REVIEW_TITLES = [
        'Absolutely worth it', 'Better than expected', 'Superb guide',
        'A genuine highlight', 'Well organised throughout', 'Do the early start',
        'Great value for money', 'Small group makes the difference',
        'Would book again', 'Excellent from start to finish',
    ];

    public function run(): void
    {
        $customers = User::where('role', UserRole::Customer)->get();
        $tours = Tour::all();

        foreach ($tours as $tour) {
            // Popular and featured tours accumulate more feedback.
            $count = $tour->is_featured ? random_int(6, 11) : random_int(3, 7);

            foreach ($customers->random(min($count, $customers->count())) as $index => $customer) {
                $rating = $this->weightedRating();

                Review::create([
                    'tour_id' => $tour->id,
                    'user_id' => $customer->id,
                    'author_name' => $customer->name,
                    'author_email' => $customer->email,
                    'author_avatar' => $customer->avatar,
                    'rating' => $rating,
                    'title' => self::REVIEW_TITLES[array_rand(self::REVIEW_TITLES)],
                    'body' => $this->body($rating),
                    'status' => $this->status($index),
                    'helpful_count' => random_int(0, 64),
                    'is_featured' => $rating === 5 && $index === 0,
                    'created_at' => now()->subDays(random_int(3, 420)),
                ]);
            }
        }
    }

    /** Skews towards 4–5 stars, which is what a curated catalogue looks like. */
    private function weightedRating(): int
    {
        return [5, 5, 5, 5, 5, 4, 4, 4, 4, 3, 3, 2][random_int(0, 11)];
    }

    private function body(int $rating): string
    {
        if ($rating <= 3) {
            return self::MIXED_REVIEWS[array_rand(self::MIXED_REVIEWS)];
        }

        return implode(' ', [
            self::POSITIVE_OPENERS[array_rand(self::POSITIVE_OPENERS)],
            self::POSITIVE_BODIES[array_rand(self::POSITIVE_BODIES)],
            self::POSITIVE_BODIES[array_rand(self::POSITIVE_BODIES)],
            self::POSITIVE_CLOSERS[array_rand(self::POSITIVE_CLOSERS)],
        ]);
    }

    /** Leaves a handful pending so the admin moderation queue is not empty. */
    private function status(int $index): ReviewStatus
    {
        return match (true) {
            $index === 2 && random_int(0, 3) === 0 => ReviewStatus::Pending,
            $index === 4 && random_int(0, 6) === 0 => ReviewStatus::Rejected,
            default => ReviewStatus::Approved,
        };
    }
}
