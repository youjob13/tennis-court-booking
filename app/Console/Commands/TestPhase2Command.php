<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Phase 2 - Task T005: Test calculateOccupiedSlots() with various booking durations
 * 
 * This command validates that multi-hour bookings correctly calculate ALL occupied slots.
 */
class TestPhase2Command extends Command
{
    protected $signature = 'test:phase2';
    protected $description = 'Test Phase 2: Multi-Hour Slot Calculation (T005)';

    public function handle(AvailabilityService $availabilityService): int
    {
        $this->info('=== Phase 2: Multi-Hour Slot Calculation Test ===');
        $this->newLine();

        // Test 1: 1-hour booking
        $this->info('Test 1: 1-hour booking (2 PM)');
        $booking1 = new Booking([
            'start_datetime' => Carbon::parse('2026-01-28 14:00:00'),
            'duration_hours' => 1,
        ]);
        $slots1 = $availabilityService->calculateOccupiedSlots($booking1);
        $this->line('  Expected: ["14:00"]');
        $this->line('  Actual:   ' . json_encode($slots1));
        $this->assertTrue(
            $slots1 === ['14:00'],
            '✓ PASS: 1-hour booking occupies 1 slot'
        );
        $this->newLine();

        // Test 2: 4-hour booking
        $this->info('Test 2: 4-hour booking (2 PM - 6 PM)');
        $booking2 = new Booking([
            'start_datetime' => Carbon::parse('2026-01-28 14:00:00'),
            'duration_hours' => 4,
        ]);
        $slots2 = $availabilityService->calculateOccupiedSlots($booking2);
        $this->line('  Expected: ["14:00", "15:00", "16:00", "17:00"]');
        $this->line('  Actual:   ' . json_encode($slots2));
        $this->assertTrue(
            $slots2 === ['14:00', '15:00', '16:00', '17:00'],
            '✓ PASS: 4-hour booking occupies 4 consecutive slots'
        );
        $this->newLine();

        // Test 3: 8-hour booking
        $this->info('Test 3: 8-hour booking (8 AM - 4 PM)');
        $booking3 = new Booking([
            'start_datetime' => Carbon::parse('2026-01-28 08:00:00'),
            'duration_hours' => 8,
        ]);
        $slots3 = $availabilityService->calculateOccupiedSlots($booking3);
        $this->line('  Expected: ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00"]');
        $this->line('  Actual:   ' . json_encode($slots3));
        $this->assertTrue(
            $slots3 === ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'],
            '✓ PASS: 8-hour booking occupies 8 consecutive slots'
        );
        $this->newLine();

        // Test 4: Edge case - booking ending at midnight
        $this->info('Test 4: Edge case - 2-hour booking crossing day boundary (11 PM - 1 AM)');
        $booking4 = new Booking([
            'start_datetime' => Carbon::parse('2026-01-28 23:00:00'),
            'duration_hours' => 2,
        ]);
        $slots4 = $availabilityService->calculateOccupiedSlots($booking4);
        $this->line('  Expected: ["23:00", "00:00"]');
        $this->line('  Actual:   ' . json_encode($slots4));
        $this->assertTrue(
            $slots4 === ['23:00', '00:00'],
            '✓ PASS: Booking crossing midnight handled correctly'
        );
        $this->newLine();

        $this->info('✅ All Phase 2 tests passed!');
        $this->newLine();

        return Command::SUCCESS;
    }

    private function assertTrue(bool $condition, string $message): void
    {
        if ($condition) {
            $this->line("<fg=green>$message</>");
        } else {
            $this->error('✗ FAIL: ' . $message);
            $this->error('Test assertion failed!');
            exit(1);
        }
    }
}
