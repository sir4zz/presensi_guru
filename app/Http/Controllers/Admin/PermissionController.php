<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Models\AttendancePermission;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FileUploadException;
use App\Services\FileUploadService;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct(protected FileUploadService $files)
    {
    }

    public function index()
    {
        $permissions = AttendancePermission::with(['guru', 'creator'])->latest('tanggal')->paginate(15);
        $gurus = User::where('role', 'guru')->get();
        $details = $this->detailMap($permissions->getCollection());
        return view('admin.permission.index', compact('permissions', 'gurus', 'details'));
    }

    /**
     * Petakan tiap izin ke record absensi terkait (sumber lampiran/lokasi
     * dinas/verifikasi). Record absensi lama bisa tidak ada — aman null.
     *
     * @param \Illuminate\Support\Collection $permissions
     * @return array<int,array<string,mixed>>
     */
    protected function detailMap($permissions): array
    {
        $map = [];
        if ($permissions->isEmpty()) {
            return $map;
        }

        $guruIds = $permissions->pluck('guru_id')->unique()->values();
        $dates = $permissions->pluck('tanggal')->map(fn ($d) => substr((string) $d, 0, 10));
        $attendances = Attendance::whereIn('guru_id', $guruIds)
            ->whereBetween('tanggal', [$dates->min(), $dates->max()])
            ->get()
            ->keyBy(fn ($a) => $a->guru_id . '|' . substr((string) $a->tanggal, 0, 10) . '|' . $a->status);

        foreach ($permissions as $perm) {
            $key = $perm->guru_id . '|' . substr((string) $perm->tanggal, 0, 10) . '|' . $perm->status;
            $att = $attendances->get($key);
            $map[$perm->id] = [
                'id' => $perm->id,
                'tanggal' => substr((string) $perm->tanggal, 0, 10),
                'guru_name' => $perm->guru->name ?? '-',
                'nip' => $perm->guru->username ?? '-',
                'status' => $perm->status,
                'alasan' => $perm->alasan,
                'creator_name' => $perm->creator->name ?? '-',
                'created_at' => $perm->created_at?->format('d M Y H:i'),
                'bukti_file' => $att?->bukti_file,
                'bukti_thumb' => $att ? FileUploadService::thumbPath($att->bukti_file) : null,
                'lokasi_dinas' => $att?->lokasi_dinas,
                'keperluan_dinas' => $att?->keperluan_dinas,
                'dinas_verified' => (bool) $att?->dinas_verified_at,
            ];
        }

        return $map;
    }

    public function store(StorePermissionRequest $request)
    {
        $data = $request->validated();
        $path = null;
        if ($request->hasFile('bukti_file')) {
            try {
                $path = $this->files->storeEvidence($request->file('bukti_file'));
            } catch (FileUploadException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        }
        $created = 0;
        $skipped = 0;
        $createdIds = [];
        foreach ($data['guru_ids'] as $guruId) {
            $guru = User::where('role', 'guru')->where('status', 'aktif')->findOrFail($guruId);
            if (Attendance::where('guru_id', $guru->id)->whereDate('tanggal', $data['tanggal'])->exists()) { $skipped++; continue; }
            $attendance = Attendance::create([
                'guru_id' => $guru->id, 'tanggal' => $data['tanggal'], 'status' => $data['status'],
                'keterangan' => $data['alasan'], 'lokasi_dinas' => $data['lokasi_dinas'] ?? null,
                'bukti_file' => $path, 'surat_tugas_file' => $data['status'] === AttendanceStatus::DinasLuar->value ? $path : null,
            ]);
            $createdIds[] = $attendance->id;
            AttendancePermission::create(['guru_id' => $guru->id, 'creator_id' => auth()->id(), 'tanggal' => $data['tanggal'], 'alasan' => $data['alasan'], 'status' => $data['status']]);
            AuditLogService::log('create', $data['status'], "Membuat {$data['status']} untuk guru: {$guru->name}", null, $attendance->toArray());
            $created++;
        }
        if ($path && $created === 0) $this->files->deleteFile($path);
        $label = $data['status'] === 'dinas_luar' ? 'Dinas Luar' : ucfirst($data['status']);
        return redirect()->route('admin.permission.index')->with('success', "{$created} {$label} dibuat." . ($skipped ? " {$skipped} dilewati karena sudah memiliki absensi." : ''));
    }

    /**
     * Edit penuh izin: alasan, tanggal, jenis, lokasi dinas, ganti lampiran.
     * Pindah tanggal/jenis memindahkan record absensi terkait secara atomik.
     */
    public function update(UpdatePermissionRequest $request, $id)
    {
        $permission = AttendancePermission::findOrFail($id);
        $oldData = $permission->toArray();
        $data = $request->validated();

        $newTanggal = \Carbon\Carbon::parse($data['tanggal'])->toDateString();

        $attendance = Attendance::where('guru_id', $permission->guru_id)
            ->whereDate('tanggal', $permission->tanggal)
            ->where('status', $permission->status ?? 'izin')
            ->first();

        // Cek bentrok bila pindah ke tanggal/jenis yang sudah ada absensinya.
        if ($attendance && ($newTanggal !== substr((string) $permission->tanggal, 0, 10) || $data['status'] !== $permission->status)) {
            $conflict = Attendance::where('guru_id', $permission->guru_id)
                ->whereDate('tanggal', $newTanggal)
                ->where('id', '!=', $attendance->id)
                ->exists();
            if ($conflict) {
                return back()->withInput()->with('error', 'Tanggal/jenis tujuan sudah memiliki data absensi.');
            }
        }

        $newPath = null;
        if ($request->hasFile('bukti_file')) {
            try {
                $newPath = $this->files->storeEvidence($request->file('bukti_file'));
            } catch (FileUploadException $e) {
                return back()->withInput()->with('error', $e->getMessage());
            }
        }

        $oldPaths = [];
        if ($attendance) {
            $oldPaths = array_filter([$attendance->bukti_file, $attendance->surat_tugas_file]);
        }

        DB::transaction(function () use ($permission, $attendance, $data, $newTanggal, $newPath) {
            $permission->update([
                'tanggal' => $newTanggal,
                'status' => $data['status'],
                'alasan' => $data['alasan'],
            ]);

            if ($attendance) {
                $statusChanged = $data['status'] !== $attendance->getOriginal('status');
                $attendance->update([
                    'tanggal' => $newTanggal,
                    'status' => $data['status'],
                    'keterangan' => $data['alasan'],
                    'lokasi_dinas' => $data['lokasi_dinas'] ?? $attendance->lokasi_dinas,
                    'bukti_file' => $newPath ?? $attendance->bukti_file,
                    'surat_tugas_file' => $data['status'] === AttendanceStatus::DinasLuar->value
                        ? ($newPath ?? $attendance->surat_tugas_file)
                        : ($statusChanged ? null : $attendance->surat_tugas_file),
                    // Ganti jenis membatalkan verifikasi dinas sebelumnya.
                    'dinas_verified_by' => $statusChanged ? null : $attendance->dinas_verified_by,
                    'dinas_verified_at' => $statusChanged ? null : $attendance->dinas_verified_at,
                ]);
            }
        });

        // Bersihkan file lama yang sudah tidak direferensikan.
        if ($newPath) {
            foreach (array_unique($oldPaths) as $old) {
                if ($old !== $newPath) {
                    $this->files->deleteFileIfOrphan($old);
                }
            }
        }

        AuditLogService::updated('izin', $permission, $oldData, $permission->fresh()->toArray(), "Mengubah izin guru: {$permission->guru->name}");

        return redirect()->route('admin.permission.index')->with('success', 'Izin berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $permission = AttendancePermission::with('guru')->findOrFail($id);
        $guruName = $permission->guru->name;
        $attendances = Attendance::where('guru_id', $permission->guru_id)->whereDate('tanggal', $permission->tanggal)->where('status', $permission->status ?? 'izin')->get();
        $paths = $attendances->flatMap(fn ($a) => [$a->bukti_file, $a->surat_tugas_file])->filter()->unique()->values();
        foreach ($attendances as $attendance) {
            $attendance->delete();
        }
        $permission->delete();

        // Hapus file fisik + thumbnail bila sudah tidak direferensikan record lain.
        foreach ($paths as $path) {
            $this->files->deleteFileIfOrphan($path);
        }

        AuditLogService::log('delete', 'izin', "Mencabut izin guru: {$guruName}");

        return redirect()->route('admin.permission.index')->with('success', 'Izin berhasil dicabut.');
    }

}
