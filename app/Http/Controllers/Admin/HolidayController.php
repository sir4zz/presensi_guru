<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHolidayRequest;
use App\Http\Requests\Admin\UpdateHolidayRequest;
use App\Models\Holiday;
use App\Services\AuditLogService;
use App\Services\HolidaySyncService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'desc')->get();
        return response()->json($holidays);
    }

    public function store(StoreHolidayRequest $request)
    {
        $items = $request->validated()['items'] ?? [];
        $seen = [];
        $created = 0;
        $skipped = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($items, &$seen, &$created, &$skipped) {
            foreach ($items as $item) {
                $date = \Carbon\Carbon::parse($item['date'])->toDateString();
                if (isset($seen[$date]) || Holiday::whereDate('date', $date)->exists()) {
                    $skipped++;
                    continue;
                }
                $seen[$date] = true;
                Holiday::create([
                    'date' => $date,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'type' => $item['type'],
                ]);
                $created++;
            }
        });

        if ($created === 0) {
            return response()->json(['success' => false, 'message' => 'Tidak ada yang ditambahkan. Semua tanggal duplikat atau sudah ada.'], 422);
        }

        AuditLogService::log('create', 'hari_libur', "Menambah hari libur massal: {$created} dibuat, {$skipped} dilewati.");

        $message = "{$created} hari libur ditambahkan."
            . ($skipped > 0 ? " {$skipped} dilewati (duplikat/sudah ada)." : '');

        return response()->json(['success' => true, 'message' => $message, 'created' => $created, 'skipped' => $skipped]);
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

    /**
     * Sinkronkan libur nasional dari API ke tabel holidays.
     */
    public function sync(Request $request, HolidaySyncService $syncService)
    {
        $year = (int) $request->input('year', now()->year);

        $result = $syncService->sync($year);

        if ($result['success']) {
            AuditLogService::created('hari_libur', new Holiday(), "Sinkronisasi libur nasional tahun {$year}: {$result['created']} ditambah, {$result['updated']} diperbarui.");
        }

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
