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
        $totalGuru = User::where('role', 'guru')->where('status', 'aktif')->count();
        $todayAttendance = Attendance::with('guru')->whereDate('tanggal', $today)->get();

        $alphaCount = $todayAttendance->where('status', 'alpha')->count();
        $stats = [
            'total' => $totalGuru,
            'hadir' => $todayAttendance->where('status', 'hadir')->count(),
            'terlambat' => $todayAttendance->where('status', 'terlambat')->count(),
            'izin' => $todayAttendance->where('status', 'izin')->count(),
            'sakit' => $todayAttendance->where('status', 'sakit')->count(),
            'alpha' => $alphaCount,
            'tak' => $alphaCount,
            'belum' => max(0, $totalGuru - $todayAttendance->count()),
            'pulang' => $todayAttendance->whereNotNull('jam_pulang')->count(),
            'dinas_luar' => $todayAttendance->where('status', 'dinas_luar')->count(),
        ];

        return view('admin.dashboard', compact('stats', 'todayAttendance'));
    }

    public function data()
    {
        $today = now()->toDateString();
        $period = request('period', 'month') === 'week' ? 'week' : 'month';

        $stats = [
            'hadir' => Attendance::whereDate('tanggal', $today)->where('status', 'hadir')->count(),
            'terlambat' => Attendance::whereDate('tanggal', $today)->where('status', 'terlambat')->count(),
            'dinas_luar' => Attendance::whereDate('tanggal', $today)->where('status', 'dinas_luar')->count(),
        ];

        if ($period === 'week') {
            $start = now()->subDays(6)->toDateString();
            $end = $today;
        } else {
            $start = now()->startOfMonth()->toDateString();
            $end = now()->endOfMonth()->toDateString();
        }

        $rows = Attendance::whereBetween('tanggal', [$start, $end])->get();

        $trend = [];
        $cursor = \Carbon\Carbon::parse($start);
        $last = \Carbon\Carbon::parse(min($end, $today));
        while ($cursor->lte($last)) {
            $date = $cursor->toDateString();
            $day = $rows->where('tanggal', $date);
            $trend[] = [
                'date' => $date,
                'label' => $cursor->locale('id')->isoFormat('D MMM'),
                'hadir' => $day->where('status', 'hadir')->count(),
                'terlambat' => $day->where('status', 'terlambat')->count(),
                'izin' => $day->where('status', 'izin')->count(),
                'sakit' => $day->where('status', 'sakit')->count(),
                'alpha' => $day->where('status', 'alpha')->count(),
                'dinas_luar' => $day->where('status', 'dinas_luar')->count(),
            ];
            $cursor->addDay();
        }

        $inPeriod = $rows->filter(fn ($a) => $a->tanggal <= $today);
        $distribution = [
            'hadir' => $inPeriod->where('status', 'hadir')->count(),
            'terlambat' => $inPeriod->where('status', 'terlambat')->count(),
            'izin' => $inPeriod->where('status', 'izin')->count(),
            'sakit' => $inPeriod->where('status', 'sakit')->count(),
            'alpha' => $inPeriod->where('status', 'alpha')->count(),
            'dinas_luar' => $inPeriod->where('status', 'dinas_luar')->count(),
        ];

        return response()->json(['stats' => $stats, 'trend' => $trend, 'distribution' => $distribution, 'period' => $period]);
    }
}
