<?php

namespace App\Enums;

use Illuminate\Database\Eloquent\Builder;

/**
 * Duration ranges offered in the tour list sidebar.
 *
 * Same-day tours store `duration_hours`; multi-day tours store `duration_days`.
 * Each case knows how to constrain a query to itself, which keeps the filter
 * logic out of the controller.
 */
enum DurationBucket: string
{
    case UpToThreeHours = '1-3-hours';
    case ThreeToSixHours = '3-6-hours';
    case SixToTwelveHours = '6-12-hours';
    case OneToThreeDays = '1-3-days';
    case FourToSevenDays = '4-7-days';
    case OverAWeek = '7-plus-days';

    public function label(): string
    {
        return match ($this) {
            self::UpToThreeHours => '1 – 3 hours',
            self::ThreeToSixHours => '3 – 6 hours',
            self::SixToTwelveHours => '6 – 12 hours',
            self::OneToThreeDays => '1 – 3 days',
            self::FourToSevenDays => '4 – 7 days',
            self::OverAWeek => 'More than a week',
        };
    }

    public function apply(Builder $query): Builder
    {
        return match ($this) {
            self::UpToThreeHours => $query->whereBetween('duration_hours', [1, 3]),
            self::ThreeToSixHours => $query->whereBetween('duration_hours', [3, 6]),
            self::SixToTwelveHours => $query->whereBetween('duration_hours', [6, 12]),
            self::OneToThreeDays => $query->whereBetween('duration_days', [1, 3]),
            self::FourToSevenDays => $query->whereBetween('duration_days', [4, 7]),
            self::OverAWeek => $query->where('duration_days', '>', 7),
        };
    }
}
