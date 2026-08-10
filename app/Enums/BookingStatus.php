<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Confirmed => 'info',
            self::Completed => 'success',
            self::Pending => 'warning',
            self::Cancelled => 'danger',
        };
    }

    /** Statuses that still count towards revenue. */
    public static function revenueStates(): array
    {
        return [self::Confirmed->value, self::Completed->value];
    }

    public static function options(): array
    {
        return array_column(array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        ), 'label', 'value');
    }
}
