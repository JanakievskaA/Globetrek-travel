<?php

namespace App\Enums;

enum TourStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Bootstrap-ish badge modifier used by the admin tables. */
    public function badge(): string
    {
        return match ($this) {
            self::Published => 'success',
            self::Draft => 'warning',
            self::Archived => 'muted',
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
