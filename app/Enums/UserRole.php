<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Customer = 'customer';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Manager => 'info',
            self::Customer => 'muted',
        };
    }

    /** Roles allowed into the admin panel. */
    public function canAccessAdmin(): bool
    {
        return $this !== self::Customer;
    }

    public static function options(): array
    {
        return array_column(array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases()
        ), 'label', 'value');
    }
}
