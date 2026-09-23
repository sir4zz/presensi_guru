<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AuditLogService;

class AttendanceController extends Controller
{
    public function index()
    {
        // Menu Absensi khusus hari ini. Rekap bulanan/tahunan + export ada di menu Laporan.
        $today = now()->toDateString();

        $status = request('status');
        if ($status === 'all' || $status === '') {
            $status = null;
        } elseif ($status === 'tidak_ada_keterangan') {
            // Nilai lama dari stat-card, di DB tersimpan sebagai 'alpha'.
            $status = 'alpha';
        } elseif ($status === 'tugas_luar') {
            $status = 'dinas_luar';
        }

        $query = Attendance::with('guru')->whereDate('tanggal', $today);

        if (request('guru_id')) $query->where('guru_id', request('guru_id'));

        $missingGurus = collect();
        if ($status === 'belum') {
            // Guru aktif yang belum punya record absensi hari ini.
            $presentIds = Attendance::whereDate('tanggal', $today)->pluck('guru_id');
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

        $attendances = $query->orderBy('jam_masuk')->paginate(20)->withQueryString();
        $gurus = User::where('role', 'guru')->orderBy('name')->get();

        return view('admin.attendance.index', compact('attendances', 'gurus', 'missingGurus', 'status', 'today'));
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
