<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['tour.destination'])
            ->search($request->string('q')->trim()->value() ?: null)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('payment'), fn ($q, $p) => $q->where('payment_status', $p))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('travel_date', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('travel_date', '<=', $d))
            // Newest booking first; id breaks ties for same-second bookings.
            ->latest()
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'statuses' => BookingStatus::options(),
            'totals' => [
                'all' => Booking::count(),
                'pending' => Booking::where('status', BookingStatus::Pending)->count(),
                'revenue' => (float) Booking::revenueGenerating()->sum('total'),
                'upcoming' => Booking::whereDate('travel_date', '>=', today())
                    ->whereIn('status', BookingStatus::revenueStates())->count(),
            ],
        ]);
    }

    public function show(Booking $booking): View
    {
        return view('admin.bookings.show', [
            'booking' => $booking->load(['tour.destination', 'user']),
            'statuses' => BookingStatus::options(),
        ]);
    }

    public function edit(Booking $booking): View
    {
        return view('admin.bookings.form', [
            'booking' => $booking->load(['tour']),
            'statuses' => BookingStatus::options(),
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_country' => ['nullable', 'string', 'max:80'],
            'travel_date' => ['required', 'date'],
            'travel_time' => ['nullable', 'string', 'max:20'],
            'adults' => ['required', 'integer', 'min:1', 'max:60'],
            'children' => ['required', 'integer', 'min:0', 'max:40'],
            'total' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(BookingStatus::class)],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'refunded'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking->update($data);

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', "Booking {$booking->reference} updated.");
    }

    /** Inline dropdown on the index table. */
    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(BookingStatus::class)]]);

        $booking->update([
            'status' => $data['status'],
            'payment_status' => match ($data['status']) {
                BookingStatus::Cancelled->value => 'refunded',
                BookingStatus::Confirmed->value, BookingStatus::Completed->value => 'paid',
                default => $booking->payment_status,
            },
        ]);

        return back()->with('success', "Booking {$booking->reference} marked as {$data['status']}.");
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $reference = $booking->reference;
        $booking->delete();

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', "Booking {$reference} was deleted.");
    }
}
