<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SpreadsheetExportService;

class AttendanceController extends Controller
{
    public function index()
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

        return view('admin.attendance.index', compact('attendances', 'gurus', 'missingGurus', 'status'));
    }

    public function export(SpreadsheetExportService $excel)
    {
        $query = Attendance::with('guru');

        if (request('guru_id')) $query->where('guru_id', request('guru_id'));
        if (request('status')) $query->where('status', request('status'));
        if (request('date_from')) $query->whereDate('tanggal', '>=', request('date_from'));
        if (request('date_to')) $query->whereDate('tanggal', '<=', request('date_to'));

        $attendances = $query->orderBy('tanggal', 'desc')->get();

        $statusLabels = [
            'hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin',
            'sakit' => 'Sakit', 'alpha' => 'TAK', 'tugas_luar' => 'Tugas Luar',
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

    public function show($id)
    {
        $attendance = Attendance::with('guru')->findOrFail($id);
        return response()->json($attendance);
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $oldData = $attendance->toArray();

        $attendance->update($request->only(['status', 'jam_masuk', 'jam_pulang', 'keterangan']));

        AuditLogService::updated('absensi', $attendance, $oldData, $attendance->fresh()->toArray(), "Koreksi absensi guru: {$attendance->guru->name}");

        return response()->json(['success' => true, 'message' => 'Koreksi berhasil disimpan.']);
    }
}
