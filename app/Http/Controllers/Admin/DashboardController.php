<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $thisMonth = Booking::revenueGenerating()
            ->whereBetween('created_at', [now()->startOfMonth(), now()])
            ->sum('total');

        $lastMonth = Booking::revenueGenerating()
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ])
            ->sum('total');

        return view('admin.dashboard', [
            'stats' => [
                'revenue' => (float) $thisMonth,
                'revenueDelta' => $this->percentChange((float) $lastMonth, (float) $thisMonth),
                'bookings' => Booking::count(),
                'pendingBookings' => Booking::where('status', BookingStatus::Pending)->count(),
                'tours' => Tour::count(),
                'publishedTours' => Tour::published()->count(),
                'customers' => User::where('role', 'customer')->count(),
                'pendingReviews' => Review::where('status', ReviewStatus::Pending)->count(),
                'destinations' => Destination::count(),
                'avgRating' => round((float) Tour::published()->avg('rating_avg'), 2),
            ],
            'revenueByMonth' => $this->revenueByMonth(),
            'recentBookings' => Booking::with(['tour'])->latest()->take(8)->get(),
            'pendingReviewList' => Review::with(['tour'])
                ->where('status', ReviewStatus::Pending)
                ->latest()->take(5)->get(),
            'topTours' => Tour::withCount(['bookings'])
                ->orderByDesc('bookings_count')
                ->take(6)->get(),
            'topDestinations' => Destination::withCount(['tours'])
                ->orderByDesc('tours_count')
                ->take(6)->get(),
        ]);
    }

    /** Confirmed + completed revenue for the last twelve months. */
    private function revenueByMonth(): array
    {
        $rows = Booking::revenueGenerating()
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->get(['created_at', 'total'])
            ->groupBy(fn (Booking $b) => $b->created_at->format('Y-m'))
            ->map(fn ($group) => (float) $group->sum('total'));

        return collect(range(11, 0))
            ->map(function (int $ago) use ($rows) {
                $month = now()->subMonths($ago)->startOfMonth();

                return [
                    'label' => $month->format('M'),
                    'value' => $rows[$month->format('Y-m')] ?? 0.0,
                ];
            })
            ->all();
    }

    private function percentChange(float $from, float $to): int
    {
        if ($from <= 0) {
            return $to > 0 ? 100 : 0;
        }

        return (int) round((($to - $from) / $from) * 100);
    }
}
