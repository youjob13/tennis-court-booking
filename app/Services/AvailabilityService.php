<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Phase 2 - Task T003: Calculate all hourly slots occupied by a multi-hour booking.
     * 
     * Purpose: For a booking with duration > 1 hour, return ALL consecutive hourly slots occupied,
     * not just the start time. This is foundational for displaying multi-hour bookings correctly.
     *
     * Example: 2 PM booking with 4 hours occupies: 14:00, 15:00, 16:00, 17:00
     *
     * @param  Booking  $booking  The booking to calculate occupied slots for
     * @return array Array of time strings in H:i format (e.g., ["14:00", "15:00", "16:00", "17:00"])
     */
    public function calculateOccupiedSlots(Booking $booking): array
    {
        $slots = [];
        $startTime = Carbon::parse($booking->start_datetime);
        
        // Generate all hourly slots from start_datetime to start_datetime + duration_hours
        for ($hour = 0; $hour < $booking->duration_hours; $hour++) {
            $slotTime = $startTime->copy()->addHours($hour);
            $slots[] = $slotTime->format('H:i');
        }
        
        return $slots;
    }

    /**
     * Get availability for a specific court on a specific date.
     *
     * Phase 2 - Task T004: Modified to use calculateOccupiedSlots() for multi-hour bookings.
     * Now correctly marks ALL consecutive slots as unavailable, not just the start time.
     *
     * @param  string  $date  Date in Y-m-d format
     * @return array ['available' => [], 'booked' => [], 'locked' => []]
     */
    public function getAvailabilityForDate(int $courtId, string $date): array
    {
        $court = \App\Models\Court::findOrFail($courtId);

        // Get operating hours or default to 08:00-22:00
        $operatingHours = $court->operating_hours ?? ['start' => '08:00', 'end' => '22:00'];

        // Generate all possible time slots
        $allSlots = $this->generateTimeSlots($date, $operatingHours['start'], $operatingHours['end']);

        // Get bookings for this court on this date
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $bookings = Booking::where('court_id', $courtId)
            ->whereBetween('start_datetime', [$startOfDay, $endOfDay])
            ->whereIn('status', ['locked', 'confirmed'])
            ->get();

        $booked = [];
        $locked = [];

        // Phase 2 - Task T004: Use calculateOccupiedSlots() for multi-hour bookings
        foreach ($bookings as $booking) {
            // Get ALL occupied slots for this booking (not just start time)
            $occupiedSlots = $this->calculateOccupiedSlots($booking);

            if ($booking->status === 'confirmed') {
                $booked = array_merge($booked, $occupiedSlots);
            } elseif ($booking->status === 'locked') {
                $locked = array_merge($locked, $occupiedSlots);
            }
        }

        // Remove duplicates and reindex arrays
        $booked = array_values(array_unique($booked));
        $locked = array_values(array_unique($locked));

        // Available slots are all slots minus booked and locked
        $unavailable = array_merge($booked, $locked);
        $available = array_diff($allSlots, $unavailable);

        return [
            'available' => array_values($available),
            'booked' => array_values($booked),
            'locked' => array_values($locked),
        ];
    }

    /**
     * Generate hourly time slots between start and end times.
     *
     * @param  string  $startTime  Format: HH:MM
     * @param  string  $endTime  Format: HH:MM
     */
    protected function generateTimeSlots(string $date, string $startTime, string $endTime): array
    {
        $slots = [];
        $current = Carbon::parse("$date $startTime");
        $end = Carbon::parse("$date $endTime");

        while ($current < $end) {
            $slots[] = $current->format('H:i');
            $current->addHour();
        }

        return $slots;
    }

    /**
     * Phase 3 - Task T006: Calculate available duration options for a given start time slot.
     *
     * Purpose: For dynamic duration dropdown - determine which durations (1-8 hours) are valid
     * from a given start time without conflicting with existing bookings.
     *
     * Algorithm:
     * 1. For each duration from 1 to 8 hours, check if all slots are available
     * 2. Stop at first conflict (durations beyond conflict are automatically invalid)
     * 3. Also check operating hours boundary
     *
     * @param  int  $courtId  The court ID
     * @param  string  $datetime  Start datetime in format Y-m-d H:i:s
     * @return array Array of valid duration integers (e.g., [1, 2, 3])
     */
    public function getAvailableDurationsForSlot(int $courtId, string $datetime): array
    {
        $court = \App\Models\Court::findOrFail($courtId);
        $startTime = Carbon::parse($datetime);
        $date = $startTime->toDateString();
        
        // Get operating hours
        $operatingHours = $court->operating_hours ?? ['start' => '08:00', 'end' => '22:00'];
        $operatingEnd = Carbon::parse($date . ' ' . $operatingHours['end']);
        
        // Get availability for the date
        $availability = $this->getAvailabilityForDate($courtId, $date);
        $unavailableSlots = array_merge($availability['booked'], $availability['locked']);
        
        $validDurations = [];
        
        // Check each duration from 1 to 8 hours
        for ($duration = 1; $duration <= 8; $duration++) {
            $endTime = $startTime->copy()->addHours($duration);
            
            // Check if end time exceeds operating hours
            if ($endTime->gt($operatingEnd)) {
                break; // Can't book beyond operating hours
            }
            
            // Check if all slots in this duration are available
            $allSlotsAvailable = true;
            for ($hour = 0; $hour < $duration; $hour++) {
                $slotTime = $startTime->copy()->addHours($hour)->format('H:i');
                if (in_array($slotTime, $unavailableSlots)) {
                    $allSlotsAvailable = false;
                    break;
                }
            }
            
            if ($allSlotsAvailable) {
                $validDurations[] = $duration;
            } else {
                break; // Stop checking longer durations if this one is blocked
            }
        }
        
        return $validDurations;
    }

    /**
     * Check if a specific time slot is available for booking.
     *
     * @param  string  $datetime  Format: Y-m-d H:i:s
     */
    public function isSlotAvailable(int $courtId, string $datetime, int $durationHours): bool
    {
        $startTime = Carbon::parse($datetime);
        $endTime = $startTime->copy()->addHours($durationHours);

        // Check if any booking overlaps with this time range
        $overlappingBookings = Booking::where('court_id', $courtId)
            ->whereIn('status', ['locked', 'confirmed'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_datetime', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime) {
                        $q->where('start_datetime', '<=', $startTime)
                            ->whereRaw('start_datetime + (duration_hours || \' hours\')::interval > ?', [$startTime]);
                    });
            })
            ->exists();

        return ! $overlappingBookings;
    }
}
