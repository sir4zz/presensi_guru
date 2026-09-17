<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHolidayRequest;
use App\Http\Requests\Admin\UpdateHolidayRequest;
use App\Models\Holiday;
use App\Services\AuditLogService;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'desc')->get();
        return response()->json($holidays);
    }

    public function store(StoreHolidayRequest $request)
    {
        $holiday = Holiday::create($request->validated());

        AuditLogService::created('hari_libur', $holiday, "Menambahkan hari libur: {$holiday->name} ({$holiday->date->format('d M Y')})");

        return response()->json(['success' => true, 'message' => 'Hari libur berhasil ditambahkan.']);
    }

    public function update(UpdateHolidayRequest $request, $id)
    {
        $holiday = Holiday::findOrFail($id);
        $oldData = $holiday->toArray();

        $holiday->update($request->validated());

        AuditLogService::updated('hari_libur', $holiday, $oldData, $holiday->fresh()->toArray(), "Memperbarui hari libur: {$holiday->name}");

        return response()->json(['success' => true, 'message' => 'Hari libur berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $name = $holiday->name;

        AuditLogService::deleted('hari_libur', $holiday, "Menghapus hari libur: {$name}");

        $holiday->delete();

        return response()->json(['success' => true, 'message' => 'Hari libur berhasil dihapus.']);
    }
}
