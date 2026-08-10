<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Destination> */
class DestinationFactory extends Factory
{
    protected $model = Destination::class;

    public function definition(): array
    {
        $city = fake()->unique()->city();

        return [
            'name' => $city,
            'country' => fake()->country(),
            'continent' => fake()->randomElement(['Europe', 'Asia', 'Africa', 'North America', 'South America']),
            'summary' => fake()->sentence(12),
            'description' => fake()->paragraphs(2, true),
            'image' => 'assets/images/travel/beach-palm-card.jpg',
            'hero_image' => 'assets/images/travel/beach-palm.jpg',
            'best_season' => 'April to October',
            'currency' => 'EUR',
            'language' => 'English',
            'timezone' => 'UTC+1',
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
