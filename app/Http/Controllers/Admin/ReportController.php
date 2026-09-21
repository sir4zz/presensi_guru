<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SpreadsheetExportService;

class ReportController extends Controller
{
    public function index()
    {
        $gurus = User::where('role', 'guru')->get();
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));
        $guruId = request('guru_id');
        $status = request('status');

        $attendances = Attendance::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->get();

        if ($guruId) {
            $gurus = $gurus->where('id', (int) $guruId)->values();
        }

        $allGurus = User::where('role', 'guru')->get();

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

        return view('admin.report.index', compact('gurus', 'allGurus', 'report', 'month', 'year'));
    }

    public function export(SpreadsheetExportService $excel)
    {
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));
        $gurus = User::where('role', 'guru')->with('guruProfile')->get();

        if (request('guru_id')) {
            $gurus = $gurus->where('id', (int) request('guru_id'))->values();
        }

        $attendances = Attendance::whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->when(request('guru_id'), fn ($q) => $q->where('guru_id', request('guru_id')))
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->get();

        $monthName = now()->locale('id')->month($month)->translatedFormat('F Y');

        $rows = [];
        foreach ($gurus as $index => $g) {
            $gAtt = $attendances->where('guru_id', $g->id);
            $total = $gAtt->count();
            $hadir = $gAtt->where('status', 'hadir')->count();
            $rows[] = [
                $index + 1,
                $g->name,
                $g->username,
                $hadir,
                $gAtt->where('status', 'terlambat')->count(),
                $gAtt->where('status', 'izin')->count(),
                $gAtt->where('status', 'sakit')->count(),
                $gAtt->where('status', 'alpha')->count(),
                $total > 0 ? round(($hadir / $total) * 100) . '%' : '0%',
            ];
        }

        AuditLogService::log('export', 'laporan', "Export laporan absensi {$monthName} ke XLSX");

        return $excel->download(
            'laporan_absensi_' . $month . '_' . $year . '.xlsx',
            ['No', 'Nama', 'NIP', 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha (TAK)', 'Persentase'],
            $rows,
            'Laporan ' . $month . '-' . $year
        );
    }
}
