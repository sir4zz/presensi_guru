<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;

class HistoryController extends Controller
{
    public function index(AttendanceService $attendanceService)
    {
        $user = auth()->user();
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));

        $history = $user->attendances()
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->latest('tanggal')
            ->get();

        $discipline = $attendanceService->getDisciplineSummary($user->id, $month, $year);
        return view('guru.history.index', compact('history', 'discipline', 'month', 'year'));
    }
}
