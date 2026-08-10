<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/** Tells the desk (admins and managers) that a customer just booked a tour. */
class NewBookingReceived extends Notification
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
            'type' => 'booking.created',
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->reference,
            'title' => 'New booking',
            'message' => sprintf(
                '%s booked %s',
                $this->booking->customer_name,
                $this->booking->tour?->title ?? 'a tour',
            ),
            'meta' => '$'.number_format((float) $this->booking->total, 2),
            'url' => route('admin.bookings.show', $this->booking),
        ];
    }
}
