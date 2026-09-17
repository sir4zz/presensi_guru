<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $todayAttendance = $user->attendances()->whereDate('tanggal', $today)->first();

        $month = now()->month;
        $year = now()->year;
        $monthAtt = $user->attendances()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();

        $monthStats = [
            'hadir' => $monthAtt->where('status', 'hadir')->count(),
            'terlambat' => $monthAtt->where('status', 'terlambat')->count(),
            'izin' => $monthAtt->where('status', 'izin')->count(),
            'sakit' => $monthAtt->where('status', 'sakit')->count(),
        ];

        $recentHistory = $user->attendances()->latest('tanggal')->limit(5)->get();

        return view('guru.dashboard', compact('todayAttendance', 'monthStats', 'recentHistory'));
    }
}
