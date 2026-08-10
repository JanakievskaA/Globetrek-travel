<?php

namespace App\Services;

use App\Models\Tour;

/**
 * Single source of truth for what a booking costs.
 *
 * The booking widget mirrors these rules in JavaScript for the live total, but
 * the figure that is stored always comes from here.
 */
class BookingPricer
{
    /** Children travel at 60% of the adult rate. */
    public const CHILD_RATE = 0.6;

    /**
     * @param  array<int, string>  $extraNames  names matching the tour's extras list
     * @return array{unit_price: float, subtotal: float, extras: array, extras_total: float, total: float}
     */
    public function quote(Tour $tour, int $adults, int $children, array $extraNames = []): array
    {
        $adults = max(1, $adults);
        $children = max(0, $children);

        $unitPrice = (float) ($tour->sale_price ?? $tour->price);
        $subtotal = ($unitPrice * $adults) + ($unitPrice * self::CHILD_RATE * $children);

        $extras = collect($tour->extras ?? [])
            ->filter(fn (array $extra) => in_array($extra['name'], $extraNames, true))
            ->values()
            ->all();

        $extrasTotal = array_sum(array_column($extras, 'price'));

        return [
            'unit_price' => round($unitPrice, 2),
            'subtotal' => round($subtotal, 2),
            'extras' => $extras,
            'extras_total' => round((float) $extrasTotal, 2),
            'total' => round($subtotal + $extrasTotal, 2),
        ];
    }

    /** Guests must fit the tour's stated maximum group size. */
    public function exceedsCapacity(Tour $tour, int $adults, int $children): bool
    {
        return ($adults + $children) > $tour->group_size;
    }
}
