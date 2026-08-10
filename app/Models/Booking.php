<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Notifications\BookingConfirmed;
use App\Notifications\NewBookingReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    /** @see self::withoutNotifications() */
    protected static bool $notifying = true;

    protected $fillable = [
        'reference', 'tour_id', 'user_id', 'customer_name', 'customer_email',
        'customer_phone', 'customer_country', 'travel_date', 'travel_time',
        'adults', 'children', 'extras', 'subtotal', 'extras_total', 'total',
        'status', 'payment_status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'travel_date' => 'date',
            'extras' => 'array',
            'subtotal' => 'decimal:2',
            'extras_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $booking) {
            $booking->reference = $booking->reference ?: self::generateReference();
        });

        // Keep the tour's cached booking counter accurate for "most booked" sorting.
        $sync = function (self $booking) {
            $booking->tour?->forceFill([
                'bookings_count' => $booking->tour->bookings()->count(),
            ])->saveQuietly();
        };

        static::saved($sync);
        static::deleted($sync);

        // The desk hears about every new booking.
        static::created(function (self $booking) {
            if (! self::$notifying) {
                return;
            }

            Notification::send(User::staff()->get(), new NewBookingReceived($booking));
        });

        /*
         * The customer hears about it once it is confirmed. Keyed off the status
         * actually changing, so re-saving a confirmed booking from the edit form
         * does not notify twice. Guests (no user_id) have nowhere to receive it.
         */
        static::updated(function (self $booking) {
            if (! self::$notifying || ! $booking->wasChanged('status')) {
                return;
            }

            if ($booking->status === BookingStatus::Confirmed) {
                $booking->user?->notify(new BookingConfirmed($booking));
            }
        });
    }

    /**
     * Runs $callback with booking notifications switched off. Seeding 180 rows
     * of history should not ring the bell 180 times.
     */
    public static function withoutNotifications(callable $callback): mixed
    {
        self::$notifying = false;

        try {
            return $callback();
        } finally {
            self::$notifying = true;
        }
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'GT-'.now()->format('y').'-'.Str::upper(Str::random(6));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('reference', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")
                ->orWhere('customer_email', 'like', "%{$term}%")
                ->orWhereHas('tour', fn (Builder $t) => $t->where('title', 'like', "%{$term}%"));
        }));
    }

    public function scopeRevenueGenerating(Builder $query): Builder
    {
        return $query->whereIn('status', BookingStatus::revenueStates());
    }

    public function getTravellersAttribute(): int
    {
        return $this->adults + $this->children;
    }
}
