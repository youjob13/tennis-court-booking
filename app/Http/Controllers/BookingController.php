<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Services\AvailabilityService;
use App\Services\BookingLockService;
use App\Services\DummyPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected $availabilityService;

    protected $lockService;

    protected $paymentService;

    public function __construct(
        AvailabilityService $availabilityService,
        BookingLockService $lockService,
        DummyPaymentService $paymentService
    ) {
        $this->availabilityService = $availabilityService;
        $this->lockService = $lockService;
        $this->paymentService = $paymentService;
    }

    /**
     * Create booking (lock time slot) and redirect to payment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'start_datetime' => 'required|date|after:now',
            'duration_hours' => 'required|integer|between:1,8',
        ]);

        $court = Court::findOrFail($validated['court_id']);

        // Check if court is active
        if (! $court->isActive()) {
            return back()->withErrors(['court_id' => 'This court is not available for booking.']);
        }

        // Phase 3 - Task T017: Enhanced validation with specific error messages
        // Check available durations for the selected start time
        $availableDurations = $this->availabilityService->getAvailableDurationsForSlot(
            $validated['court_id'],
            $validated['start_datetime']
        );
        
        // Specific error message based on failure type
        if (empty($availableDurations)) {
            // Slot is completely unavailable (start time is booked/locked)
            $booking = \App\Models\Booking::where('court_id', $validated['court_id'])
                ->where('start_datetime', '<=', $validated['start_datetime'])
                ->whereIn('status', ['locked', 'confirmed'])
                ->whereRaw('start_datetime + (duration_hours || \' hours\')::interval > ?', [$validated['start_datetime']])
                ->first();
            
            if ($booking && $booking->status === 'locked') {
                return back()->withErrors([
                    'start_datetime' => 'This time slot is temporarily reserved by another user. Please wait or select a different time.'
                ])->withInput();
            } else {
                return back()->withErrors([
                    'start_datetime' => 'This time slot is already booked. Please select a different time.'
                ])->withInput();
            }
        }
        
        // Check if requested duration is available
        if (!in_array($validated['duration_hours'], $availableDurations)) {
            $maxDuration = max($availableDurations);
            return back()->withErrors([
                'duration_hours' => "Selected duration conflicts with existing bookings. Maximum available duration is {$maxDuration} hour(s)."
            ])->withInput();
        }

        // Validate operating hours
        $startTime = \Carbon\Carbon::parse($validated['start_datetime']);
        $operatingHours = $court->operating_hours ?? ['start' => '08:00', 'end' => '22:00'];
        $operatingStart = \Carbon\Carbon::parse($startTime->toDateString() . ' ' . $operatingHours['start']);
        $operatingEnd = \Carbon\Carbon::parse($startTime->toDateString() . ' ' . $operatingHours['end']);

        if ($startTime->lt($operatingStart) || $startTime->gte($operatingEnd)) {
            return back()->withErrors([
                'start_datetime' => 'Booking must be within operating hours (' .
                    $operatingHours['start'] . ' - ' . $operatingHours['end'] . ').',
            ])->withInput();
        }

        // Acquire lock and create booking
        try {
            $booking = $this->lockService->acquireLock(
                $validated['court_id'],
                Auth::id(),
                $validated['start_datetime'],
                $validated['duration_hours'],
                $court->hourly_price
            );

            return redirect()->route('bookings.payment', $booking->id);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Unable to lock booking: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display payment form.
     */
    public function showPayment(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        // Check if booking is still locked
        if (! $booking->isLocked()) {
            return redirect()->route('courts.index')
                ->with('error', 'Booking is no longer locked.');
        }

        // Check if lock has expired
        if ($booking->hasExpiredLock()) {
            return redirect()->route('courts.index')
                ->with('error', 'Booking lock has expired. Please try again.');
        }

        return view('bookings.payment', compact('booking'));
    }

    /**
     * Process payment and confirm booking.
     */
    public function processPayment(Request $request, Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        // Check if booking is still locked
        if (! $booking->isLocked()) {
            return redirect()->route('courts.index')
                ->with('error', 'Booking is no longer locked.');
        }

        // Check if lock has expired
        if ($booking->hasExpiredLock()) {
            $booking->update(['status' => 'cancelled']);

            return redirect()->route('courts.index')
                ->with('error', 'Booking lock has expired. Please try again.');
        }

        $validated = $request->validate([
            'card_number' => 'required|string',
            'card_expiry' => 'required|string',
            'card_cvv' => 'required|string',
        ]);

        // Process dummy payment
        $paymentResult = $this->paymentService->processPayment(
            $booking->id,
            $booking->total_price,
            $validated
        );

        if ($paymentResult['success']) {
            // Confirm booking
            $booking->update([
                'status' => 'confirmed',
                'payment_reference' => $paymentResult['reference'],
                'lock_expires_at' => null,
                'unlocked_after' => null,
            ]);

            return redirect()->route('bookings.confirmation', $booking->id);
        } else {
            // Payment failed - set unlock delay
            $booking->update([
                'unlocked_after' => now()->addSeconds(30),
            ]);

            return back()->withErrors(['payment' => 'Payment failed: ' . $paymentResult['message']])->withInput();
        }
    }

    /**
     * Display booking confirmation.
     */
    public function showConfirmation(Booking $booking)
    {
        // Ensure user owns this booking
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        // Ensure booking is confirmed
        if (! $booking->isConfirmed()) {
            return redirect()->route('courts.index')
                ->with('error', 'Booking is not confirmed.');
        }

        return view('bookings.confirmation', compact('booking'));
    }
}
