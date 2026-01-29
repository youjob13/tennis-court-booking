<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Services\AvailabilityService;

class CourtController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Display listing of all active courts with availability.
     */
    public function index()
    {
        $courts = Court::active()
            ->with(['bookings' => function ($query) {
                $query->whereDate('start_datetime', now()->toDateString())
                    ->whereIn('status', ['confirmed', 'locked']);
            }])
            ->get();

        // Calculate availability for today for each court
        $today = now()->toDateString();

        foreach ($courts as $court) {
            $availability = $this->availabilityService->getAvailabilityForDate($court->id, $today);
            $court->available_slots_today = $availability['available'];
            $court->booked_slots_today = $availability['booked'];
            $court->locked_slots_today = $availability['locked'];
        }

        return view('courts.index', compact('courts'));
    }

    /**
     * Display court details with booking form.
     */
    public function show(Court $court)
    {
        // Get availability for next 7 days
        $availability = [];
        for ($i = 0; $i < 7; $i++) {
            $date = now()->addDays($i)->toDateString();
            $availability[$date] = $this->availabilityService->getAvailabilityForDate($court->id, $date);
        }

        return view('courts.show', compact('court', 'availability'));
    }

    /**
     * Phase 3 - Task T009: API endpoint to get available durations for a selected start time.
     *
     * Purpose: AJAX endpoint for dynamic duration dropdown validation (US1).
     * Returns valid duration options based on slot availability.
     *
     * @param  Court  $court  Route model binding
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableDurations(Court $court, \Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'datetime' => 'required|date|after:now',
        ]);

        // Get valid durations from service
        $durations = $this->availabilityService->getAvailableDurationsForSlot(
            $court->id,
            $validated['datetime']
        );

        $maxDuration = !empty($durations) ? max($durations) : 0;
        $reason = null;

        // Provide explanation if max duration is less than 8
        if ($maxDuration > 0 && $maxDuration < 8) {
            $conflictTime = \Carbon\Carbon::parse($validated['datetime'])->addHours($maxDuration + 1);
            $reason = ($maxDuration + 1) . '+ hours conflicts with existing booking at ' . $conflictTime->format('g A');
        } elseif ($maxDuration === 0) {
            $reason = 'This time slot is not available for booking.';
        }

        return response()->json([
            'durations' => $durations,
            'max_duration' => $maxDuration,
            'reason' => $reason,
        ]);
    }
}
