<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Models\AttendancePermission;
use App\Models\User;
use App\Services\AuditLogService;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = AttendancePermission::with(['guru', 'creator'])->latest('tanggal')->paginate(15);
        $gurus = User::where('role', 'guru')->get();
        return view('admin.permission.index', compact('permissions', 'gurus'));
    }

    public function store(StorePermissionRequest $request)
    {
        $data = $request->validated();
        $path = $request->hasFile('bukti_file') ? $request->file('bukti_file')->store('attendance/evidence', 'public') : null;
        $created = 0;
        $skipped = 0;
        foreach ($data['guru_ids'] as $guruId) {
            $guru = User::where('role', 'guru')->where('status', 'aktif')->findOrFail($guruId);
            if (Attendance::where('guru_id', $guru->id)->whereDate('tanggal', $data['tanggal'])->exists()) { $skipped++; continue; }
            $attendance = Attendance::create([
                'guru_id' => $guru->id, 'tanggal' => $data['tanggal'], 'status' => $data['status'],
                'keterangan' => $data['alasan'], 'lokasi_dinas' => $data['lokasi_dinas'] ?? null,
                'bukti_file' => $path, 'surat_tugas_file' => $data['status'] === AttendanceStatus::DinasLuar->value ? $path : null,
            ]);
            AttendancePermission::create(['guru_id' => $guru->id, 'creator_id' => auth()->id(), 'tanggal' => $data['tanggal'], 'alasan' => $data['alasan'], 'status' => $data['status']]);
            AuditLogService::log('create', $data['status'], "Membuat {$data['status']} untuk guru: {$guru->name}", null, $attendance->toArray());
            $created++;
        }
        if ($skipped > 0 && $created === 0 && $path) Storage::disk('public')->delete($path);
        $label = $data['status'] === 'dinas_luar' ? 'Dinas Luar' : ucfirst($data['status']);
        return redirect()->route('admin.permission.index')->with('success', "{$created} {$label} dibuat." . ($skipped ? " {$skipped} dilewati karena sudah memiliki absensi." : ''));
    }

    public function destroy($id)
    {
        $permission = AttendancePermission::with('guru')->findOrFail($id);
        $guruName = $permission->guru->name;
        Attendance::where('guru_id', $permission->guru_id)->whereDate('tanggal', $permission->tanggal)->where('status', $permission->status ?? 'izin')->delete();
        $permission->delete();

        AuditLogService::log('delete', 'izin', "Mencabut izin guru: {$guruName}");

        return redirect()->route('admin.permission.index')->with('success', 'Izin berhasil dicabut.');
    }

}
