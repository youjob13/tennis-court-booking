<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingLockService
{
    /**
     * Acquire lock for a time slot using SELECT FOR UPDATE.
     *
     * @throws \Exception
     */
    public function acquireLock(int $courtId, int $userId, string $startDatetime, int $durationHours, float $hourlyPrice): Booking
    {
        return DB::transaction(function () use ($courtId, $userId, $startDatetime, $durationHours, $hourlyPrice) {
            // Lock the row to prevent concurrent bookings
            $existingBooking = Booking::where('court_id', $courtId)
                ->where('start_datetime', $startDatetime)
                ->whereIn('status', ['locked', 'confirmed'])
                ->lockForUpdate()
                ->first();

            if ($existingBooking) {
                throw new \Exception('Time slot is already booked or locked.');
            }

            // Create locked booking
            $booking = Booking::create([
                'court_id' => $courtId,
                'user_id' => $userId,
                'start_datetime' => $startDatetime,
                'duration_hours' => $durationHours,
                'total_price' => $hourlyPrice * $durationHours,
                'status' => 'locked',
                'lock_expires_at' => now()->addMinutes(10),
                'unlocked_after' => null,
                'payment_reference' => null,
            ]);

            return $booking;
        });
    }

    /**
     * Release expired locks.
     *
     * @return int Number of released locks
     */
    public function releaseExpiredLocks(): int
    {
        return Booking::where('status', 'locked')
            ->where(function ($query) {
                $query->where('lock_expires_at', '<', now())
                    ->orWhere(function ($q) {
                        $q->whereNotNull('unlocked_after')
                            ->where('unlocked_after', '<', now());
                    });
            })
            ->update(['status' => 'cancelled']);
    }
}
