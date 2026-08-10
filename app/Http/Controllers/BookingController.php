<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Tour;
use App\Services\BookingPricer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly BookingPricer $pricer) {}

    /** Checkout page — pre-filled from the tour page booking widget. */
    public function create(Tour $tour, Request $request): View
    {
        abort_unless($tour->status->value === 'published', 404);

        $adults = max(1, (int) $request->input('adults', 2));
        $children = max(0, (int) $request->input('children', 0));
        $extras = (array) $request->input('extras', []);

        return view('pages.bookings.create', [
            'tour' => $tour->load(['destination', 'category', 'images']),
            'quote' => $this->pricer->quote($tour, $adults, $children, $extras),
            'prefill' => [
                'travel_date' => $request->input('travel_date'),
                'travel_time' => $request->input('travel_time'),
                'adults' => $adults,
                'children' => $children,
                'extras' => $extras,
            ],
        ]);
    }

    public function store(StoreBookingRequest $request, Tour $tour): RedirectResponse
    {
        abort_unless($tour->status->value === 'published', 404);

        $adults = (int) $request->integer('adults');
        $children = (int) $request->integer('children');
        $extraNames = (array) $request->input('extras', []);

        if ($this->pricer->exceedsCapacity($tour, $adults, $children)) {
            return back()
                ->withInput()
                ->with('error', "This tour takes a maximum of {$tour->group_size} guests per departure.");
        }

        $quote = $this->pricer->quote($tour, $adults, $children, $extraNames);

        $booking = Booking::create([
            'tour_id' => $tour->id,
            'user_id' => $request->user()?->id,
            'customer_name' => $request->string('customer_name'),
            'customer_email' => $request->string('customer_email'),
            'customer_phone' => $request->string('customer_phone'),
            'customer_country' => $request->string('customer_country'),
            'travel_date' => $request->date('travel_date'),
            'travel_time' => $request->string('travel_time'),
            'adults' => $adults,
            'children' => $children,
            'extras' => $quote['extras'],
            'subtotal' => $quote['subtotal'],
            'extras_total' => $quote['extras_total'],
            'total' => $quote['total'],
            'status' => BookingStatus::Pending,
            'payment_status' => 'unpaid',
            'notes' => $request->string('notes'),
        ]);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Your booking request is in — reference '.$booking->reference.'.');
    }

    public function show(Booking $booking): View
    {
        return view('pages.bookings.show', [
            'booking' => $booking->load(['tour.destination', 'tour.category']),
        ]);
    }
}
