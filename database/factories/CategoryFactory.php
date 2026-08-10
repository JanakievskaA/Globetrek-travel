<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'icon' => 'assets/images/icons/beach.svg',
            'image' => 'assets/images/travel/beach-palm-card.jpg',
            'description' => fake()->sentence(14),
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
