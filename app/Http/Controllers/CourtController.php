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
}
