<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Phase 3 - Task T007 & T010: Test getAvailableDurationsForSlot() and API endpoint
 *
 * This command tests dynamic duration validation with various booking scenarios
 */
class TestPhase3Command extends Command
{
    protected $signature = 'test:phase3 {--cleanup : Clean up test bookings after tests}';
    protected $description = 'Test Phase 3: Pre-Booking Validation (T007, T010)';

    public function handle(AvailabilityService $availabilityService): int
    {
        $this->info('=== Phase 3: Pre-Booking Validation Test ===');
        $this->newLine();

        // Get or create test court
        $court = Court::first();
        if (!$court) {
            $this->error('No courts found. Please seed courts first.');
            return Command::FAILURE;
        }

        $this->info("Using court: {$court->name} (ID: {$court->id})");
        $this->newLine();

        // Get or create test user
        $user = User::first();
        if (!$user) {
            $this->error('No users found. Please seed users first.');
            return Command::FAILURE;
        }

        $testDate = now()->addDays(1)->toDateString();
        $createdBookings = [];

        try {
            // Test 1: No conflicts - all durations available
            $this->info('Test 1: No conflicts (empty schedule)');
            $testTime1 = Carbon::parse("$testDate 14:00:00");
            $durations1 = $availabilityService->getAvailableDurationsForSlot($court->id, $testTime1->format('Y-m-d H:i:s'));
            $this->line('  Start time: 2:00 PM');
            $this->line('  Available durations: ' . json_encode($durations1));
            $this->assertTrue(
                count($durations1) >= 1,
                '✓ PASS: At least 1 duration available'
            );
            $this->newLine();

            // Create a test booking: 4:00 PM - 8:00 PM (4 hours)
            $this->info('Creating test booking: 4:00 PM - 8:00 PM (4 hours)');
            $booking1 = Booking::create([
                'court_id' => $court->id,
                'user_id' => $user->id,
                'start_datetime' => Carbon::parse("$testDate 16:00:00"),
                'duration_hours' => 4,
                'total_price' => $court->hourly_price * 4,
                'status' => 'confirmed',
            ]);
            $createdBookings[] = $booking1->id;
            $this->line('  Booking created (ID: ' . $booking1->id . ')');
            $this->newLine();

            // Test 2: Partial conflict - limited durations
            $this->info('Test 2: Partial conflict (booking at 4 PM blocks 3+ hours from 2 PM)');
            $testTime2 = Carbon::parse("$testDate 14:00:00");
            $durations2 = $availabilityService->getAvailableDurationsForSlot($court->id, $testTime2->format('Y-m-d H:i:s'));
            $this->line('  Start time: 2:00 PM');
            $this->line('  Available durations: ' . json_encode($durations2));
            $this->line('  Expected: [1, 2] (3+ hours would overlap with 4 PM booking)');
            $this->assertTrue(
                in_array(1, $durations2) && in_array(2, $durations2) && !in_array(3, $durations2),
                '✓ PASS: Only 1-2 hours available (3+ blocked)'
            );
            $this->newLine();

            // Test 3: All blocked - no durations available
            $this->info('Test 3: All blocked (start time is already booked)');
            $testTime3 = Carbon::parse("$testDate 16:00:00"); // Same as booking start
            $durations3 = $availabilityService->getAvailableDurationsForSlot($court->id, $testTime3->format('Y-m-d H:i:s'));
            $this->line('  Start time: 4:00 PM (already booked)');
            $this->line('  Available durations: ' . json_encode($durations3));
            $this->assertTrue(
                empty($durations3),
                '✓ PASS: No durations available (slot is booked)'
            );
            $this->newLine();

            // Test 4: API endpoint test (T010)
            $this->info('Test 4: API endpoint test (GET /api/courts/{court}/availability/durations)');
            $apiUrl = url("/api/courts/{$court->id}/availability/durations");
            $testTime4 = Carbon::parse("$testDate 14:00:00");
            $response = Http::get($apiUrl, [
                'datetime' => $testTime4->format('Y-m-d H:i:s'),
            ]);

            $this->line('  API URL: ' . $apiUrl);
            $this->line('  Response status: ' . $response->status());

            if ($response->successful()) {
                $apiData = $response->json();
                $this->line('  Response body: ' . json_encode($apiData));
                $this->assertTrue(
                    isset($apiData['durations']) && isset($apiData['max_duration']),
                    '✓ PASS: API returns correct structure'
                );
                $this->assertTrue(
                    $apiData['durations'] === $durations2,
                    '✓ PASS: API durations match service method'
                );
            } else {
                $this->error('✗ FAIL: API request failed');
                return Command::FAILURE;
            }
            $this->newLine();

            // Test 5: Operating hours boundary
            $this->info('Test 5: Operating hours boundary (9 PM slot, limited durations)');
            $operatingEnd = $court->operating_hours['end'] ?? '22:00';
            $endHour = (int) substr($operatingEnd, 0, 2);
            $testTime5 = Carbon::parse("$testDate " . ($endHour - 1) . ":00:00"); // 1 hour before close
            $durations5 = $availabilityService->getAvailableDurationsForSlot($court->id, $testTime5->format('Y-m-d H:i:s'));
            $this->line('  Start time: ' . $testTime5->format('g A'));
            $this->line('  Operating hours end: ' . $operatingEnd);
            $this->line('  Available durations: ' . json_encode($durations5));
            $this->assertTrue(
                max($durations5 ?: [0]) <= 1,
                '✓ PASS: Durations respect operating hours'
            );
            $this->newLine();

            $this->info('✅ All Phase 3 tests passed!');
            $this->newLine();

        } finally {
            // Cleanup test bookings
            if ($this->option('cleanup') && !empty($createdBookings)) {
                $this->info('Cleaning up test bookings...');
                Booking::whereIn('id', $createdBookings)->delete();
                $this->line('  Deleted ' . count($createdBookings) . ' test booking(s)');
            } elseif (!empty($createdBookings)) {
                $this->warn('Test bookings created (IDs: ' . implode(', ', $createdBookings) . ')');
                $this->warn('Run with --cleanup flag to remove them: php artisan test:phase3 --cleanup');
            }
        }

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
