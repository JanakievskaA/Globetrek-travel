<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_submitted_review_waits_for_moderation(): void
    {
        $tour = Tour::factory()->create();

        $this->post(route('reviews.store', $tour), [
            'author_name' => 'Marie Curie',
            'author_email' => 'marie@example.com',
            'rating' => 5,
            'body' => 'An outstanding trip that was very well organised from beginning to end.',
        ])->assertSessionHas('success');

        $review = Review::firstOrFail();
        $this->assertSame(ReviewStatus::Pending, $review->status);

        // Pending reviews must not appear publicly or move the rating.
        $this->get(route('tours.show', $tour))->assertDontSee('An outstanding trip');
        $this->assertSame(0.0, $tour->fresh()->rating_avg);
    }

    public function test_short_reviews_are_rejected(): void
    {
        $tour = Tour::factory()->create();

        $this->post(route('reviews.store', $tour), [
            'author_name' => 'Marie Curie',
            'author_email' => 'marie@example.com',
            'rating' => 5,
            'body' => 'Great',
        ])->assertSessionHasErrors('body');

        $this->assertSame(0, Review::count());
    }

    public function test_rating_cache_reflects_approved_reviews_only(): void
    {
        $tour = Tour::factory()->create();

        Review::factory()->for($tour)->rated(5)->create();
        Review::factory()->for($tour)->rated(3)->create();
        Review::factory()->for($tour)->rated(1)->pending()->create();

        $tour->refresh();

        $this->assertSame(2, $tour->reviews_count);
        $this->assertSame(4.0, $tour->rating_avg);
    }

    public function test_deleting_a_review_updates_the_cache(): void
    {
        $tour = Tour::factory()->create();
        Review::factory()->for($tour)->rated(5)->create();
        $second = Review::factory()->for($tour)->rated(1)->create();

        $this->assertSame(3.0, $tour->fresh()->rating_avg);

        $second->delete();

        $this->assertSame(5.0, $tour->fresh()->rating_avg);
        $this->assertSame(1, $tour->fresh()->reviews_count);
    }
}
