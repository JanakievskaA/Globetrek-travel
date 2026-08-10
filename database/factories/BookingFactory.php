<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Booking> */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(100, 3000);

        return [
            'tour_id' => Tour::factory(),
            'user_id' => null,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_country' => fake()->country(),
            'travel_date' => now()->addDays(30),
            'travel_time' => '09:00',
            'adults' => 2,
            'children' => 0,
            'extras' => [],
            'subtotal' => $subtotal,
            'extras_total' => 0,
            'total' => $subtotal,
            'status' => BookingStatus::Pending,
            'payment_status' => 'unpaid',
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Confirmed,
            'payment_status' => 'paid',
        ]);
    }
}
