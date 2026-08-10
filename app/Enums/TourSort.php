<?php

namespace App\Enums;

use Illuminate\Database\Eloquent\Builder;

enum TourSort: string
{
    case Recommended = 'recommended';
    case PriceLowHigh = 'price-asc';
    case PriceHighLow = 'price-desc';
    case RatingHighLow = 'rating-desc';
    case Newest = 'newest';
    case DurationShort = 'duration-asc';
    case MostBooked = 'popular';

    public function label(): string
    {
        return match ($this) {
            self::Recommended => 'Sort by (Default)',
            self::PriceLowHigh => 'Price: low to high',
            self::PriceHighLow => 'Price: high to low',
            self::RatingHighLow => 'Top rated',
            self::Newest => 'Newest first',
            self::DurationShort => 'Duration: shortest',
            self::MostBooked => 'Most booked',
        };
    }

    public function apply(Builder $query): Builder
    {
        return match ($this) {
            self::Recommended => $query->orderByDesc('is_featured')->orderByDesc('rating_avg'),
            self::PriceLowHigh => $query->orderBy('effective_price'),
            self::PriceHighLow => $query->orderByDesc('effective_price'),
            self::RatingHighLow => $query->orderByDesc('rating_avg')->orderByDesc('reviews_count'),
            self::Newest => $query->orderByDesc('created_at'),
            self::DurationShort => $query->orderBy('duration_days')->orderBy('duration_hours'),
            self::MostBooked => $query->orderByDesc('bookings_count'),
        };
    }

    public static function options(): array
    {
        return array_column(array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        ), 'label', 'value');
    }
}
