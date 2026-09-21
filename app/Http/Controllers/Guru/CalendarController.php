<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Holiday;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));

        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        $prevUrl = route('guru.calendar.index', ['month' => $prevMonth, 'year' => $prevYear]);
        $nextUrl = route('guru.calendar.index', ['month' => $nextMonth, 'year' => $nextYear]);
        $todayUrl = route('guru.calendar.index');

        $monthNames = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $monthName = $monthNames[$month] . ' ' . $year;

        $startDate = now()->year($year)->month($month)->startOfMonth()->toDateString();
        $endDate = now()->year($year)->month($month)->endOfMonth()->toDateString();

        $monthAtt = $user->attendances()
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

        $monthStats = [
            'hadir' => $monthAtt->where('status', 'hadir')->count(),
            'terlambat' => $monthAtt->where('status', 'terlambat')->count(),
            'izin' => $monthAtt->where('status', 'izin')->count(),
            'sakit' => $monthAtt->where('status', 'sakit')->count(),
            'total_hari_kerja' => $monthAtt->pluck('tanggal')->unique()->count(),
        ];

        $attendanceData = $monthAtt->mapWithKeys(function ($att) {
            return [$att->tanggal => [
                'status' => $att->status,
                'jam_masuk' => $att->jam_masuk,
                'jam_pulang' => $att->jam_pulang,
                'foto_masuk' => $att->foto_masuk,
                'lat_masuk' => $att->lat_masuk,
                'lng_masuk' => $att->lng_masuk,
                'distance_masuk' => $att->distance_masuk,
            ]];
        })->toArray();

        $holidayMap = $holidays->keyBy(fn ($h) => $h->date->format('Y-m-d'))->toArray();

        return view('guru.calendar.index', compact('monthStats', 'attendanceData', 'holidayMap', 'month', 'year', 'prevUrl', 'nextUrl', 'todayUrl', 'monthName'));
    }
}
