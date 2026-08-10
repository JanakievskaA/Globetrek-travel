<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_renders_with_a_quote(): void
    {
        $tour = Tour::factory()->create(['price' => 300]);

        $this->get(route('bookings.create', [$tour, 'adults' => 2, 'children' => 1]))
            ->assertOk()
            ->assertSee($tour->title)
            // 2 × 300 + 1 × 180 = 780
            ->assertSee('780.00');
    }

    public function test_a_booking_is_created_with_server_calculated_totals(): void
    {
        $tour = Tour::factory()->create([
            'price' => 400,
            'group_size' => 10,
            'extras' => [['name' => 'Travel insurance', 'price' => 28]],
        ]);

        $response = $this->post(route('bookings.store', $tour), [
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'travel_date' => now()->addDays(30)->toDateString(),
            'adults' => 2,
            'children' => 1,
            'extras' => ['Travel insurance'],
        ]);

        $booking = Booking::firstOrFail();

        $response->assertRedirect(route('bookings.show', $booking));

        // 2 × 400 + 1 × 240 = 1040, plus a 28 extra.
        $this->assertEquals(1040.00, (float) $booking->subtotal);
        $this->assertEquals(28.00, (float) $booking->extras_total);
        $this->assertEquals(1068.00, (float) $booking->total);
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertMatchesRegularExpression('/^GT-\d{2}-[A-Z0-9]{6}$/', $booking->reference);
    }

    public function test_client_supplied_totals_are_ignored(): void
    {
        $tour = Tour::factory()->create(['price' => 500, 'extras' => []]);

        $this->post(route('bookings.store', $tour), [
            'customer_name' => 'Grace Hopper',
            'customer_email' => 'grace@example.com',
            'travel_date' => now()->addDays(10)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'total' => 1,          // a tampered price must not be trusted
            'subtotal' => 1,
        ]);

        $this->assertEquals(500.00, (float) Booking::firstOrFail()->total);
    }

    public function test_unknown_extras_are_not_chargeable(): void
    {
        $tour = Tour::factory()->create([
            'price' => 100,
            'extras' => [['name' => 'Travel insurance', 'price' => 28]],
        ]);

        $this->post(route('bookings.store', $tour), [
            'customer_name' => 'Alan Turing',
            'customer_email' => 'alan@example.com',
            'travel_date' => now()->addDays(10)->toDateString(),
            'adults' => 1,
            'children' => 0,
            'extras' => ['Free helicopter'],
        ]);

        $booking = Booking::firstOrFail();
        $this->assertSame([], $booking->extras);
        $this->assertEquals(100.00, (float) $booking->total);
    }

    public function test_booking_is_rejected_when_the_group_is_too_large(): void
    {
        $tour = Tour::factory()->create(['group_size' => 4]);

        $this->post(route('bookings.store', $tour), [
            'customer_name' => 'Katherine Johnson',
            'customer_email' => 'katherine@example.com',
            'travel_date' => now()->addDays(10)->toDateString(),
            'adults' => 6,
            'children' => 2,
        ])->assertSessionHas('error');

        $this->assertSame(0, Booking::count());
    }

    public function test_past_departure_dates_are_rejected(): void
    {
        $tour = Tour::factory()->create();

        $this->post(route('bookings.store', $tour), [
            'customer_name' => 'Ada Lovelace',
            'customer_email' => 'ada@example.com',
            'travel_date' => now()->subDay()->toDateString(),
            'adults' => 1,
        ])->assertSessionHasErrors('travel_date');

        $this->assertSame(0, Booking::count());
    }

    public function test_confirmation_page_shows_the_reference(): void
    {
        $booking = Booking::factory()->create();

        $this->get(route('bookings.show', $booking))
            ->assertOk()
            ->assertSee($booking->reference);
    }

    public function test_booking_count_cache_is_maintained(): void
    {
        $tour = Tour::factory()->create();

        Booking::factory()->count(3)->for($tour)->create();

        $this->assertSame(3, $tour->fresh()->bookings_count);
    }
}
