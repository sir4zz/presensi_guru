<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FileUploadException;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function __construct(protected FileUploadService $files)
    {
    }
    public function index()
    {
        // Menu Absensi mendukung filter hari (default hari ini).
        $today = request('tanggal') && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) request('tanggal'))
            ? request('tanggal')
            : now()->toDateString();

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
        $data = $request->validated();

        $isHadir = in_array($data['status'], ['hadir', 'terlambat'], true);
        $isDinas = $data['status'] === 'dinas_luar';

        // Ganti ke non-Hadir mengosongkan jam otomatis (jam tak bermakna
        // untuk izin/sakit/TAK/dinas).
        $payload = [
            'status' => $data['status'],
            'jam_masuk' => $isHadir ? ($data['jam_masuk'] ?? null) ?: null : null,
            'jam_pulang' => $isHadir ? ($data['jam_pulang'] ?? null) ?: null : null,
            'keterangan' => $data['keterangan'] ?? null,
        ];

        if ($isDinas) {
            $payload['keperluan_dinas'] = $data['keperluan_dinas'] ?? $attendance->keperluan_dinas;
            $payload['lokasi_dinas'] = $data['lokasi_dinas'] ?? $attendance->lokasi_dinas;
        }

        $newPath = null;
        $oldPaths = [];
        if ($request->hasFile('bukti_file')) {
            try {
                $newPath = $this->files->storeEvidence($request->file('bukti_file'));
            } catch (FileUploadException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            $payload['bukti_file'] = $newPath;
            $payload['surat_tugas_file'] = $newPath;
            $oldPaths = array_filter([$attendance->bukti_file, $attendance->surat_tugas_file]);
        }

        DB::transaction(function () use ($attendance, $payload) {
            $attendance->update($payload);
        });

        foreach (array_unique($oldPaths) as $old) {
            if ($old !== $newPath) {
                $this->files->deleteFileIfOrphan($old);
            }
        }

        $newData = $attendance->fresh()->toArray();
        $alasan = $data['alasan_koreksi'] ?? '';
        $newData['alasan_koreksi'] = $alasan;

        AuditLogService::updated('absensi', $attendance, $oldData, $newData, "Koreksi absensi guru: {$attendance->guru->name} — Alasan: {$alasan}");

        return response()->json(['success' => true, 'message' => 'Koreksi berhasil disimpan.']);
    }

    /** Hapus satu record absensi beserta file-filenya. */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $label = "absensi {$attendance->guru->name} ({$attendance->tanggal}, {$attendance->status})";
        $paths = array_filter([
            $attendance->foto_masuk, $attendance->foto_pulang,
            $attendance->bukti_file, $attendance->surat_tugas_file,
        ]);

        DB::transaction(function () use ($attendance) {
            $attendance->delete();
        });

        $cleaned = 0;
        foreach (array_unique($paths) as $path) {
            if (! Attendance::where('foto_masuk', $path)->orWhere('foto_pulang', $path)
                ->orWhere('bukti_file', $path)->orWhere('surat_tugas_file', $path)->exists()) {
                $this->files->deleteFile($path);
                $cleaned++;
            }
        }

        AuditLogService::log('delete', 'absensi', "Menghapus {$label} + {$cleaned} file dibersihkan.");

        return back()->with('success', 'Data absensi dihapus.');
    }

    /** Hapus massal record terpilih (checkbox), atomik. */
    public function bulkDestroy()
    {
        $data = request()->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'integer|exists:attendances,id',
        ]);

        $attendances = Attendance::with('guru')->whereIn('id', $data['ids'])->get();
        $paths = $attendances->flatMap(fn ($a) => [
            $a->foto_masuk, $a->foto_pulang, $a->bukti_file, $a->surat_tugas_file,
        ])->filter()->unique()->values();

        DB::transaction(function () use ($attendances) {
            Attendance::whereIn('id', $attendances->pluck('id'))->delete();
        });

        $cleaned = $this->cleanFiles($paths);

        AuditLogService::log('delete', 'absensi', "Hapus massal absensi: {$attendances->count()} record + {$cleaned} file dibersihkan.");

        return back()->with('success', "{$attendances->count()} data absensi dihapus.");
    }

    /** Hapus file-file yang sudah tidak direferensikan record mana pun. */
    protected function cleanFiles($paths): int
    {
        $cleaned = 0;
        foreach ($paths as $path) {
            if (! Attendance::where('foto_masuk', $path)->orWhere('foto_pulang', $path)
                ->orWhere('bukti_file', $path)->orWhere('surat_tugas_file', $path)->exists()) {
                $this->files->deleteFile($path);
                $cleaned++;
            }
        }
        return $cleaned;
    }
}
