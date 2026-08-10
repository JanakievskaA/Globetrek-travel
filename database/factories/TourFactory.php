<?php

namespace Database\Factories;

use App\Enums\TourStatus;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tour> */
class TourFactory extends Factory
{
    protected $model = Tour::class;

    public function definition(): array
    {
        return [
            'title' => ucfirst(fake()->unique()->words(4, true)),
            'destination_id' => Destination::factory(),
            'category_id' => Category::factory(),
            'summary' => fake()->sentence(16),
            'description' => fake()->paragraphs(3, true),
            'image' => 'assets/images/travel/beach-palm-card.jpg',
            'price' => fake()->numberBetween(60, 2500),
            'sale_price' => null,
            'duration_days' => 3,
            'duration_nights' => 2,
            'duration_hours' => null,
            'group_size' => 12,
            'min_age' => 0,
            'difficulty' => 'easy',
            'departure_point' => fake()->city(),
            'contact_phone' => '+1 (229) 555-0109',
            'languages' => ['English'],
            'includes' => ['Licensed local guide'],
            'excludes' => ['International flights'],
            'highlights' => ['A memorable highlight'],
            'amenities' => ['Professional guide'],
            'faqs' => [],
            'extras' => [['name' => 'Travel insurance', 'price' => 28]],
            'is_featured' => false,
            'status' => TourStatus::Published,
        ];
    }

    public function dayTrip(int $hours = 6): static
    {
        return $this->state(fn () => [
            'duration_days' => 0,
            'duration_nights' => 0,
            'duration_hours' => $hours,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => TourStatus::Draft]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function onSale(float $salePrice): static
    {
        return $this->state(fn () => ['sale_price' => $salePrice]);
    }
}
