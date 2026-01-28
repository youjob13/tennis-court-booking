<?php

namespace App\Console\Commands;

use App\Services\BookingLockService;
use Illuminate\Console\Command;

class ReleaseExpiredLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:release-expired-locks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release expired booking locks and unlock payment-failed bookings';

    /**
     * The booking lock service.
     */
    protected BookingLockService $lockService;

    /**
     * Create a new command instance.
     */
    public function __construct(BookingLockService $lockService)
    {
        parent::__construct();
        $this->lockService = $lockService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired booking locks...');

        $releasedCount = $this->lockService->releaseExpiredLocks();

        if ($releasedCount > 0) {
            $this->info("Released {$releasedCount} expired booking lock(s).");
        } else {
            $this->info('No expired locks found.');
        }

        return 0;
    }
}
