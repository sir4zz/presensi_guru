<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));

        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }

        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        $prevUrl = route('admin.calendar.index', ['month' => $prevMonth, 'year' => $prevYear]);
        $nextUrl = route('admin.calendar.index', ['month' => $nextMonth, 'year' => $nextYear]);
        $todayUrl = route('admin.calendar.index');

        $monthNames = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $monthName = $monthNames[$month] . ' ' . $year;

        $startDate = now()->year($year)->month($month)->startOfMonth()->toDateString();
        $endDate = now()->year($year)->month($month)->endOfMonth()->toDateString();

        $attendances = Attendance::with('guru')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get();

        $gurus = User::where('role', 'guru')->where('status', 'aktif')->get();

        $monthStats = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'total_guru' => $gurus->count(),
        ];

        $calendarData = [];
        foreach ($attendances as $att) {
            $date = $att->tanggal;
            if (!isset($calendarData[$date])) {
                $calendarData[$date] = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'total' => 0];
            }
            $calendarData[$date][$att->status] = ($calendarData[$date][$att->status] ?? 0) + 1;
            $calendarData[$date]['total']++;
        }

        $holidayMap = $holidays->keyBy(fn ($h) => $h->date->format('Y-m-d'))->toArray();

        return view('admin.calendar.index', compact(
            'month', 'year', 'calendarData', 'holidayMap', 'monthStats', 'gurus',
            'prevUrl', 'nextUrl', 'todayUrl', 'monthName'
        ));
    }

    public function dayDetail($date)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $date)) {
            return response()->json(['message' => 'Format tanggal tidak valid.'], 422);
        }

        $day = Carbon::parse($date);

        $attendances = Attendance::with('guru.guruProfile')
            ->whereDate('tanggal', $date)
            ->get();

        $holiday = Holiday::whereDate('date', $date)->first();

        $dayOfWeek = $day->locale('id')->isoFormat('dddd');
        $isWeekend = $day->dayOfWeek === 0 || $day->dayOfWeek === 6;
        $isSunday = $day->dayOfWeek === 0;

        return response()->json([
            'date' => $date,
            'day_name' => $dayOfWeek,
            'is_weekend' => $isWeekend,
            'is_sunday' => $isSunday,
            'holiday' => $holiday,
            'attendances' => $attendances->map(fn ($att) => [
                'id' => $att->id,
                'guru_name' => $att->guru->name ?? '-',
                'nip' => $att->guru->username ?? '-',
                'status' => $att->status,
                'jam_masuk' => $att->jam_masuk,
                'jam_pulang' => $att->jam_pulang,
                'foto_masuk' => $att->foto_masuk,
                'foto_pulang' => $att->foto_pulang,
                'distance_masuk' => $att->distance_masuk,
                'distance_pulang' => $att->distance_pulang,
                'keterangan' => $att->keterangan,
            ]),
        ]);
    }
}
