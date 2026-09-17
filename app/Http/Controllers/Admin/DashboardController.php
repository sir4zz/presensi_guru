<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $totalGuru = User::where('role', 'guru')->count();
        $todayAttendance = Attendance::with('guru')->whereDate('tanggal', $today)->get();

        $stats = [
            'total' => $totalGuru,
            'hadir' => $todayAttendance->where('status', 'hadir')->count(),
            'terlambat' => $todayAttendance->where('status', 'terlambat')->count(),
            'izin' => $todayAttendance->where('status', 'izin')->count(),
            'sakit' => $todayAttendance->where('status', 'sakit')->count(),
            'alpha' => $todayAttendance->where('status', 'alpha')->count(),
            'belum' => $totalGuru - $todayAttendance->count(),
            'pulang' => $todayAttendance->whereNotNull('jam_pulang')->count(),
        ];

        return view('admin.dashboard', compact('stats', 'todayAttendance'));
    }

    public function data()
    {
        $today = now()->toDateString();
        $stats = [
            'hadir' => Attendance::whereDate('tanggal', $today)->where('status', 'hadir')->count(),
            'terlambat' => Attendance::whereDate('tanggal', $today)->where('status', 'terlambat')->count(),
        ];
        return response()->json(['stats' => $stats]);
    }
}
