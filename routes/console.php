<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sinkronisasi libur nasional tiap awal bulan (Asia/Jakarta)
// agar perubahan SKB ikut terbawa tanpa input manual.
Schedule::command('holidays:sync')
    ->monthlyOn(1, '01:00')
    ->timezone('Asia/Jakarta');
