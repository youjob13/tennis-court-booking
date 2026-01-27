<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class AvailabilityService
{
    /**
     * Get availability for a specific court on a specific date.
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

        foreach ($bookings as $booking) {
            $slotTime = $booking->start_datetime->format('H:i');

            if ($booking->status === 'confirmed') {
                $booked[] = $slotTime;
            } elseif ($booking->status === 'locked') {
                $locked[] = $slotTime;
            }
        }

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
