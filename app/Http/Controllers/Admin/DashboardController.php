<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with statistics.
     */
    public function index(): View
    {
        $stats = [
            'total_courts' => Court::count(),
            'active_courts' => Court::where('status', 'active')->count(),
            'disabled_courts' => Court::where('status', 'disabled')->count(),
            'total_users' => User::where('role', 'user')->count(),
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'locked_bookings' => Booking::where('status', 'locked')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'today_revenue' => Booking::where('status', 'confirmed')
                ->whereDate('created_at', today())
                ->sum('total_price'),
            'total_revenue' => Booking::where('status', 'confirmed')->sum('total_price'),
        ];

        $recent_bookings = Booking::with(['court', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_bookings'));
    }
}
