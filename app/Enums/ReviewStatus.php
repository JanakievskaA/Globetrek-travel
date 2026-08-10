<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Pending => 'warning',
            self::Rejected => 'danger',
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
