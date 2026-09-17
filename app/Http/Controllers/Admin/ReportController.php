<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;

class ReportController extends Controller
{
    public function index()
    {
        $gurus = User::where('role', 'guru')->get();
        $month = request('month', now()->month);
        $year = request('year', now()->year);

        $attendances = Attendance::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get();

        $report = [
            'total_hari_kerja' => $attendances->pluck('tanggal')->unique()->count(),
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'guru_data' => $gurus->map(function ($g) use ($attendances) {
                $gAtt = $attendances->where('guru_id', $g->id);
                $total = $gAtt->count();
                $hadir = $gAtt->where('status', 'hadir')->count();
                $terlambat = $gAtt->where('status', 'terlambat')->count();
                return [
                    'name' => $g->name,
                    'nip' => $g->username,
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'izin' => $gAtt->where('status', 'izin')->count(),
                    'sakit' => $gAtt->where('status', 'sakit')->count(),
                    'tak' => $gAtt->where('status', 'alpha')->count(),
                    'persentase' => $total > 0 ? round(($hadir / $total) * 100) : 0,
                ];
            }),
        ];

        return view('admin.report.index', compact('gurus', 'report'));
    }

    public function export()
    {
        $month = (int) request('month', now()->month);
        $year = (int) request('year', now()->year);
        $gurus = User::where('role', 'guru')->with('guruProfile')->get();

        $attendances = Attendance::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->get();

        $monthName = now()->setMonth($month)->format('F Y');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="laporan_absensi_' . $month . '_' . $year . '.csv"',
        ];

        $callback = function () use ($gurus, $attendances, $monthName) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Laporan Absensi Guru - ' . $monthName]);
            fputcsv($file, []);
            fputcsv($file, ['No', 'Nama', 'NIP', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha (TAK)', 'Persentase']);

            $no = 1;
            foreach ($gurus as $g) {
                $gAtt = $attendances->where('guru_id', $g->id);
                $total = $gAtt->count();
                $hadir = $gAtt->where('status', 'hadir')->count();

                fputcsv($file, [
                    $no++,
                    $g->name,
                    $g->username,
                    $hadir,
                    $gAtt->where('status', 'terlambat')->count(),
                    $gAtt->where('status', 'izin')->count(),
                    $gAtt->where('status', 'sakit')->count(),
                    $gAtt->where('status', 'alpha')->count(),
                    $total > 0 ? round(($hadir / $total) * 100) . '%' : '0%',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
