<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Tells the customer their booking moved from pending to confirmed. */
class BookingConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    /** In-app only: nothing here goes out by email. */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Stored as a snapshot rather than a booking id, so the entry still reads
     * correctly after the booking is edited or deleted.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking.confirmed',
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->reference,
            'title' => 'Booking confirmed',
            'message' => sprintf(
                'Your trip %s is confirmed.',
                $this->booking->tour?->title ?? '',
            ),
            'meta' => $this->booking->travel_date?->format('j M Y'),
            'url' => route('bookings.show', $this->booking),
        ];
    }
}
