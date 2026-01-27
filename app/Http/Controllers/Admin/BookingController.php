<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of locked bookings.
     */
    public function index(): View
    {
        $bookings = Booking::with(['court', 'user'])
            ->where('status', 'locked')
            ->orWhere(function ($query) {
                $query->where('status', 'confirmed')
                    ->where('start_datetime', '>', now());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Cancel a booking (only locked bookings).
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        // Prevent cancelling confirmed bookings
        if ($booking->status === 'confirmed') {
            return redirect()->route('admin.bookings.index')
                ->withErrors(['error' => 'Cannot cancel confirmed bookings. Only locked bookings can be cancelled.']);
        }

        $booking->update(['status' => 'cancelled']);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking cancelled successfully.');
    }
}
