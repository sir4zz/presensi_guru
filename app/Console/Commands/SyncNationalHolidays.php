<?php

namespace App\Console\Commands;

use App\Services\HolidaySyncService;
use Illuminate\Console\Command;

class SyncNationalHolidays extends Command
{
    protected $signature = 'holidays:sync {year? : Tahun yang disinkronkan (default: tahun berjalan)}';
    protected $description = 'Sinkronkan libur nasional Indonesia dari API ke tabel holidays';

    public function handle(HolidaySyncService $syncService): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);

        $this->info("Menyinkronkan libur nasional tahun {$year}...");

        $result = $syncService->sync($year);

        if (!$result['success']) {
            $this->error($result['message']);
            return Command::FAILURE;
        }

        $this->info($result['message']);

        return Command::SUCCESS;
    }
}
