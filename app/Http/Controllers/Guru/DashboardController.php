<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;

class DashboardController extends Controller
{
    public function index(AttendanceService $attendanceService)
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

        $checkoutWindow = $attendanceService->getCheckoutWindow();
        $checkoutStart = $checkoutWindow['start'];
        $checkoutEnd = $checkoutWindow['end'];
        $checkoutStatus = $checkoutWindow['status'];
        $canCheckout = $checkoutWindow['can_checkout'];

        $redDate = $attendanceService->isRedDate(now()->toDateString());

        return view('guru.dashboard', compact('todayAttendance', 'monthStats', 'recentHistory', 'checkoutStart', 'checkoutEnd', 'checkoutStatus', 'canCheckout', 'redDate'));
    }
}
