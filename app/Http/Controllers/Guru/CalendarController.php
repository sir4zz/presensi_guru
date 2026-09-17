<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $month = request('month', now()->month);
        $year = request('year', now()->year);

        $monthAtt = $user->attendances()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get();

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
            ]];
        })->toArray();

        return view('guru.calendar.index', compact('monthStats', 'attendanceData'));
    }
}
