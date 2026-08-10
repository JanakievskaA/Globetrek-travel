<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    private const TIMES = ['07:00', '08:30', '09:00', '10:00', '13:30', '15:00'];

    private const NOTES = [
        null, null, null,
        'Celebrating a 40th birthday — any small touch appreciated.',
        'One vegetarian and one coeliac in the party.',
        'Arriving on the morning flight, please allow for a late pickup.',
        'Honeymoon trip.',
        'Travelling with a 9-year-old, please confirm suitability.',
    ];

    public function run(): void
    {
        // Seeded history is backfill, not desk activity: no bells for any of it.
        Booking::withoutNotifications(fn () => $this->seedBookings());
    }

    private function seedBookings(): void
    {
        $customers = User::where('role', UserRole::Customer)->get();
        $tours = Tour::all();

        // 180 bookings spread across the last year and the next four months.
        foreach (range(1, 180) as $i) {
            $tour = $tours->random();
            $customer = $customers->random();

            $isPast = $i % 10 < 6;
            $travelDate = $isPast
                ? now()->subDays(random_int(1, 330))
                : now()->addDays(random_int(1, 120));

            $adults = random_int(1, min(4, $tour->group_size));
            $children = random_int(0, 1) ? random_int(0, 2) : 0;

            $unitPrice = (float) ($tour->sale_price ?? $tour->price);
            $subtotal = $unitPrice * $adults + ($unitPrice * 0.6 * $children);

            $extras = $this->pickExtras($tour);
            $extrasTotal = array_sum(array_column($extras, 'price'));

            $status = $this->status($isPast, $i);

            // Booked well before departure, but never in the future: a seeded row
            // dated ahead of today would outrank real bookings in the admin list.
            $bookedAt = (clone $travelDate)->subDays(random_int(7, 90));

            if ($bookedAt->isFuture()) {
                $bookedAt = now()->subDays(random_int(1, 30))->subHours(random_int(0, 23));
            }

            Booking::create([
                'tour_id' => $tour->id,
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'customer_country' => $customer->country,
                'travel_date' => $travelDate,
                'travel_time' => self::TIMES[array_rand(self::TIMES)],
                'adults' => $adults,
                'children' => $children,
                'extras' => $extras,
                'subtotal' => round($subtotal, 2),
                'extras_total' => round($extrasTotal, 2),
                'total' => round($subtotal + $extrasTotal, 2),
                'status' => $status,
                'payment_status' => $this->paymentStatus($status),
                'notes' => self::NOTES[array_rand(self::NOTES)],
                'created_at' => $bookedAt,
            ]);
        }
    }

    private function pickExtras(Tour $tour): array
    {
        $available = $tour->extras ?? [];

        if ($available === [] || random_int(0, 2) === 0) {
            return [];
        }

        return collect($available)
            ->shuffle()
            ->take(random_int(1, 2))
            ->values()
            ->all();
    }

    private function status(bool $isPast, int $index): BookingStatus
    {
        if ($isPast) {
            return $index % 12 === 0 ? BookingStatus::Cancelled : BookingStatus::Completed;
        }

        return match ($index % 5) {
            0 => BookingStatus::Pending,
            1 => BookingStatus::Pending,
            4 => BookingStatus::Cancelled,
            default => BookingStatus::Confirmed,
        };
    }

    private function paymentStatus(BookingStatus $status): string
    {
        return match ($status) {
            BookingStatus::Completed, BookingStatus::Confirmed => 'paid',
            BookingStatus::Cancelled => 'refunded',
            BookingStatus::Pending => 'unpaid',
        };
    }
}
