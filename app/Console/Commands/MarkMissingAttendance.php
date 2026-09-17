<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;

class MarkMissingAttendance extends Command
{
    protected $signature = 'attendance:mark-missing';
    protected $description = 'Mark guru who didn\'t check in yesterday as alpha';

    public function handle(AttendanceService $attendanceService): int
    {
        $count = $attendanceService->markMissing();
        $this->info("Marked {$count} guru as alpha for yesterday.");
        return Command::SUCCESS;
    }
}
