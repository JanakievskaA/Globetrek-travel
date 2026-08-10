<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AccountBookingController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.account.bookings', [
            'bookings' => $request->user()
                ->bookings()
                ->with(['tour.destination'])
                // Most recently booked first; id breaks ties for same-second bookings.
                ->latest()
                ->latest('id')
                ->paginate(10),
        ]);
    }
}
