<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Models\AttendancePermission;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FileUploadException;
use App\Services\FileUploadService;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;

class PermissionController extends Controller
{
    public function __construct(protected FileUploadService $files)
    {
    }

    public function index()
    {
        $permissions = AttendancePermission::with(['guru', 'creator'])->latest('tanggal')->paginate(15);
        $gurus = User::where('role', 'guru')->get();
        return view('admin.permission.index', compact('permissions', 'gurus'));
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
