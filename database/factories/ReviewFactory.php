<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Review;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Review> */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'tour_id' => Tour::factory(),
            'user_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'rating' => fake()->numberBetween(3, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(4),
            'status' => ReviewStatus::Approved,
            'helpful_count' => 0,
            'is_featured' => false,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => ReviewStatus::Pending]);
    }

    public function rated(int $rating): static
    {
        return $this->state(fn () => ['rating' => $rating]);
    }
}
