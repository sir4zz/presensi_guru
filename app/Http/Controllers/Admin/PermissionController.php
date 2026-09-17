<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Models\AttendancePermission;
use App\Models\User;
use App\Services\AuditLogService;

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
        foreach ($request->guru_ids as $guruId) {
            $guru = User::find($guruId);
            AttendancePermission::create([
                'guru_id' => $guruId,
                'creator_id' => auth()->id(),
                'tanggal' => $request->tanggal,
                'alasan' => $request->alasan,
            ]);

            AuditLogService::log('create', 'izin', "Memberikan izin kepada guru: {$guru->name} tanggal {$request->tanggal}");
        }

        return redirect()->route('admin.permission.index')->with('success', 'Izin berhasil diberikan.');
    }

    public function destroy($id)
    {
        $permission = AttendancePermission::with('guru')->findOrFail($id);
        $guruName = $permission->guru->name;
        $permission->delete();

        AuditLogService::log('delete', 'izin', "Mencabut izin guru: {$guruName}");

        return redirect()->route('admin.permission.index')->with('success', 'Izin berhasil dicabut.');
    }
}
