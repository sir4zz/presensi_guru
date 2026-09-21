<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SpreadsheetExportService;
use Illuminate\Support\Facades\Storage;
use App\Services\AttendanceService;
use App\Services\AttendanceReportService;

class AttendanceController extends Controller
{
    public function index(AttendanceService $attendanceService, AttendanceReportService $reports)
    {
        $status = request('status');
        if ($status === 'all' || $status === '') {
            $status = null;
        } elseif ($status === 'tidak_ada_keterangan') {
            // Nilai lama dari stat-card, di DB tersimpan sebagai 'alpha'.
            $status = 'alpha';
        }

        $query = Attendance::with('guru');

        if (request('guru_id')) $query->where('guru_id', request('guru_id'));
        if (request('date')) $query->whereDate('tanggal', request('date'));

        $missingGurus = collect();
        if ($status === 'belum') {
            // Guru aktif yang belum punya record absensi pada tanggal filter (default hari ini).
            $date = request('date', now()->toDateString());
            $presentIds = Attendance::whereDate('tanggal', $date)->pluck('guru_id');
            $missingGurus = User::where('role', 'guru')
                ->where('status', 'aktif')
                ->whereNotIn('id', $presentIds)
                ->when(request('guru_id'), fn ($q) => $q->where('id', request('guru_id')))
                ->orderBy('name')
                ->get();
            // Tidak ada baris absensi untuk status ini; yang ditampilkan daftar guru di bawah.
            $query->whereRaw('0 = 1');
        } elseif ($status === 'pulang') {
            $query->whereNotNull('jam_pulang');
        } elseif ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->latest('tanggal')->paginate(20)->withQueryString();
        $gurus = User::where('role', 'guru')->get();
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));
        $monthlyGurus = request('guru_id') ? $gurus->where('id', (int) request('guru_id'))->values() : $gurus;
        // Rekap bulanan terpusat: 1 agregasi (tanpa N+1 per guru).
        $agg = $reports->monthlyReport($month, $year, request('guru_id') ? (int) request('guru_id') : null, null);
        $aggById = collect($agg['rows'])->keyBy('id');
        $monthlyReport = $monthlyGurus->map(function ($guru) use ($aggById) {
            $row = $aggById->get($guru->id);
            return [
                'guru' => $guru,
                'summary' => [
                    'terlambat_menit' => $row['tm'] ?? 0,
                    'pulang_awal_menit' => $row['ps'] ?? 0,
                    'konversi_hari' => $row['konversi_hari'] ?? 0,
                    'tmtb' => $row['tmtb'] ?? 0,
                    'total_hari' => $row['total'] ?? 0,
                ],
                'attendanceCount' => $row['apel'] ?? 0,
            ];
        });

        return view('admin.attendance.index', compact('attendances', 'gurus', 'missingGurus', 'status', 'monthlyReport', 'month', 'year'));
    }

    public function export(SpreadsheetExportService $excel)
    {
        if (request('period') === 'monthly') {
            return $this->exportMonthly($excel, app(AttendanceService::class));
        }
        if (request('period') === 'yearly') {
            return $this->exportYearly($excel, app(AttendanceService::class));
        }
        $query = Attendance::with('guru');

        if (request('guru_id')) $query->where('guru_id', request('guru_id'));
        if (request('status')) $query->where('status', request('status'));
        if (request('date_from')) $query->whereDate('tanggal', '>=', request('date_from'));
        if (request('date_to')) $query->whereDate('tanggal', '<=', request('date_to'));

        $attendances = $query->orderBy('tanggal', 'desc')->get();

        $statusLabels = [
            'hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin',
            'sakit' => 'Sakit', 'alpha' => 'TAK', 'tugas_luar' => 'Tugas Luar', 'dinas_luar' => 'Dinas Luar',
            'tidak_ada_keterangan' => 'TAK',
        ];

        $rows = [];
        foreach ($attendances as $index => $att) {
            $rows[] = [
                $index + 1,
                $att->guru->name ?? '-',
                $att->guru->username ?? '-',
                $att->tanggal,
                $statusLabels[$att->status] ?? $att->status,
                $att->jam_masuk ?? '-',
                $att->jam_pulang ?? '-',
                $att->keterangan ?? '-',
            ];
        }

        AuditLogService::log('export', 'absensi', 'Export data absensi ke XLSX: ' . count($rows) . ' data');

        return $excel->download(
            'absensi_' . now()->format('Y-m-d') . '.xlsx',
            ['No', 'Nama', 'NIP', 'Tanggal', 'Status', 'Jam Masuk', 'Jam Pulang', 'Keterangan'],
            $rows,
            'Data Absensi'
        );
    }

    protected function exportMonthly(SpreadsheetExportService $excel, AttendanceService $attendanceService)
    {
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));
        $reports = app(AttendanceReportService::class);
        $guruId = request('guru_id') ? (int) request('guru_id') : null;
        $agg = $reports->monthlyReport($month, $year, $guruId, null);
        $rows = [];
        foreach ($agg['rows'] as $row) {
            $rows[] = [$row['no'], $row['nip'], $row['name'], $row['apel'], $row['tm'], $row['ps'], round($row['konversi_hari']), $row['tmtb'], $row['total']];
        }
        AuditLogService::log('export', 'absensi', "Export rekap bulanan {$month}-{$year} ke XLSX");
        return $excel->download('laporan_kehadiran_' . $month . '_' . $year . '.xlsx', ['NO', 'NIP', 'NAMA', 'APEL', 'TM', 'PS', 'JML HARI', 'TMTB', 'TOTAL'], $rows, 'Laporan Kehadiran');
    }

    protected function exportYearly(SpreadsheetExportService $excel, AttendanceService $attendanceService)
    {
        $year = min(2100, max(2000, (int) request('year', now()->year)));
        $reports = app(AttendanceReportService::class);
        $guruId = request('guru_id') ? (int) request('guru_id') : null;
        $agg = $reports->yearlyReport($year, $guruId, null);
        $rows = [];
        foreach ($agg['rows'] as $row) {
            $rows[] = [$row['no'], $row['nip'], $row['name'], $row['apel'], $row['tm'], $row['ps'], round($row['konversi_hari']), $row['tmtb'], $row['total']];
        }
        AuditLogService::log('export', 'absensi', "Export rekap tahunan {$year} ke XLSX");
        return $excel->download('laporan_kehadiran_' . $year . '.xlsx', ['NO', 'NIP', 'NAMA', 'APEL', 'TM', 'PS', 'JML HARI', 'TMTB', 'TOTAL'], $rows, 'Laporan Kehadiran Tahunan');
    }

    public function show($id)
    {
        $attendance = Attendance::with('guru')->findOrFail($id);
        return view('admin.attendance.show', compact('attendance'));
    }

    public function verifyDinas(Attendance $attendance)
    {
        abort_unless($attendance->status === 'dinas_luar', 422);
        $attendance->update(['dinas_verified_by' => auth()->id(), 'dinas_verified_at' => now()]);
        AuditLogService::log('verifikasi_dinas', 'absensi', 'Memverifikasi Dinas Luar #' . $attendance->id);
        return back()->with('success', 'Dinas Luar berhasil diverifikasi.');
    }

    public function rejectDinas(Attendance $attendance)
    {
        abort_unless($attendance->status === 'dinas_luar', 422);
        $attendance->update(['dinas_verified_by' => null, 'dinas_verified_at' => null, 'keterangan' => 'Dinas Luar ditolak oleh admin.']);
        AuditLogService::log('verifikasi_dinas', 'absensi', 'Menolak Dinas Luar #' . $attendance->id);
        return back()->with('success', 'Dinas Luar ditolak.');
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $oldData = $attendance->toArray();

        $attendance->update($request->only(['status', 'jam_masuk', 'jam_pulang', 'keterangan']));

        $newData = $attendance->fresh()->toArray();
        $alasan = $request->validated()['alasan_koreksi'] ?? '';
        $newData['alasan_koreksi'] = $alasan;

        AuditLogService::updated('absensi', $attendance, $oldData, $newData, "Koreksi absensi guru: {$attendance->guru->name} — Alasan: {$alasan}");

        return response()->json(['success' => true, 'message' => 'Koreksi berhasil disimpan.']);
    }
}
