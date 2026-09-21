<?php

namespace App\Http\Controllers\Guru;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreDinasLuarRequest;
use App\Models\Attendance;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Storage;

class DinasLuarController extends Controller
{
    public function create()
    {
        $requests = auth()->user()->attendances()->where('status', AttendanceStatus::DinasLuar->value)->latest('tanggal')->get();
        return view('guru.dinas-luar.index', compact('requests'));
    }

    public function store(StoreDinasLuarRequest $request)
    {
        $data = $request->validated();
        $existing = Attendance::where('guru_id', $request->user()->id)->whereDate('tanggal', $data['tanggal'])->first();
        if ($existing) return back()->withInput()->with('error', 'Tanggal tersebut sudah memiliki data absensi.');
        $path = $request->file('bukti_file')->store('attendance/evidence', 'public');
        $attendance = Attendance::create([
            'guru_id' => $request->user()->id,
            'tanggal' => $data['tanggal'],
            'status' => AttendanceStatus::DinasLuar->value,
            'keperluan_dinas' => $data['keperluan_dinas'],
            'lokasi_dinas' => $data['lokasi_dinas'],
            'surat_tugas_file' => $path,
            'bukti_file' => $path,
        ]);
        AuditLogService::log('upload_bukti', 'absensi', 'Pengajuan Dinas Luar dan upload bukti: ' . $attendance->id, null, $attendance->toArray());
        return redirect()->route('guru.kedinasan.index')->with('success', 'Pengajuan Dinas Luar berhasil dikirim.');
    }

    public function destroy(Attendance $attendance)
    {
        abort_unless($attendance->guru_id === auth()->id() && $attendance->status === AttendanceStatus::DinasLuar->value, 403);
        if ($attendance->bukti_file) Storage::disk('public')->delete($attendance->bukti_file);
        $attendance->delete();
        AuditLogService::log('hapus_bukti', 'absensi', 'Menghapus bukti Dinas Luar');
        return back()->with('success', 'Pengajuan Dinas Luar dihapus.');
    }
}
