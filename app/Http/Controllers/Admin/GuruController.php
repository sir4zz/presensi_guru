<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGuruRequest;
use App\Http\Requests\Admin\UpdateGuruRequest;
use App\Models\GuruProfile;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SpreadsheetExportService;
use Illuminate\Support\Facades\Hash;

class GuruController extends Controller
{
    public function index()
    {
        $gurus = User::where('role', 'guru')->with('guruProfile')->paginate(15);
        return view('admin.guru.index', compact('gurus'));
    }

    public function create()
    {
        return view('admin.guru.create');
    }

    public function store(StoreGuruRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['nip'],
            'password' => Hash::make($validated['password']),
            'role' => 'guru',
            'status' => 'aktif',
        ]);

        GuruProfile::create([
            'user_id' => $user->id,
            'nip' => $validated['nip'],
            'sk' => $validated['sk'] ?? null,
            'spmt' => $validated['spmt'] ?? null,
        ]);

        AuditLogService::created('guru', $user, "Menambahkan guru baru: {$user->name}");

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function show($id)
    {
        $guru = User::where('role', 'guru')->with('guruProfile')->findOrFail($id);
        $month = now()->month;
        $year = now()->year;
        $attendances = $guru->attendances()->whereMonth('tanggal', $month)->whereYear('tanggal', $year)->get();
        $stats = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];
        $recentAttendance = $guru->attendances()->latest('tanggal')->limit(10)->get();

        return view('admin.guru.show', compact('guru', 'stats', 'recentAttendance'));
    }

    public function edit($id)
    {
        $guru = User::where('role', 'guru')->with('guruProfile')->findOrFail($id);
        return view('admin.guru.edit', compact('guru'));
    }

    public function update(UpdateGuruRequest $request, $id)
    {
        $validated = $request->validated();
        $guru = User::where('role', 'guru')->findOrFail($id);
        $oldData = $guru->toArray();

        $data = [
            'name' => $validated['name'],
            'username' => $validated['nip'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $guru->update($data);

        GuruProfile::updateOrCreate(
            ['user_id' => $guru->id],
            ['nip' => $validated['nip'], 'sk' => $validated['sk'] ?? null, 'spmt' => $validated['spmt'] ?? null]
        );

        AuditLogService::updated('guru', $guru, $oldData, $guru->fresh()->toArray(), "Memperbarui data guru: {$guru->name}");

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil diupdate.');
    }

    public function destroy($id)
    {
        $guru = User::where('role', 'guru')->findOrFail($id);
        $name = $guru->name;
        $guru->delete();

        AuditLogService::log('delete', 'guru', "Menghapus guru: {$name}");

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil dihapus.');
    }

    public function import()
    {
        request()->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = request()->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $name = trim($data['name'] ?? $data['nama'] ?? '');
            $nip = trim($data['nip'] ?? $data['username'] ?? '');
            $sk = trim($data['sk'] ?? '');
            $spmt = trim($data['spmt'] ?? '');

            if (!$name || !$nip) {
                $skipped++;
                continue;
            }

            if (User::where('username', $nip)->exists()) {
                $skipped++;
                continue;
            }

            $user = User::create([
                'name' => $name,
                'username' => $nip,
                'password' => Hash::make('password'),
                'role' => 'guru',
                'status' => 'aktif',
            ]);

            GuruProfile::create([
                'user_id' => $user->id,
                'nip' => $nip,
                'sk' => $sk ?: null,
                'spmt' => $spmt ?: null,
            ]);

            $imported++;
        }

        fclose($handle);

        AuditLogService::log('import', 'guru', "Import guru dari CSV: {$imported} berhasil, {$skipped} dilewati");

        return redirect()->route('admin.guru.index')->with('success', "Import selesai: {$imported} guru berhasil diimport, {$skipped} dilewati.");
    }

    public function export(SpreadsheetExportService $excel)
    {
        $gurus = User::where('role', 'guru')->with('guruProfile')->orderBy('name')->get();

        $rows = [];
        foreach ($gurus as $index => $guru) {
            $rows[] = [
                $index + 1,
                $guru->name,
                $guru->username,
                $guru->guruProfile?->sk ?? '-',
                $guru->guruProfile?->spmt ?? '-',
                $guru->status === 'aktif' ? 'Aktif' : 'Nonaktif',
            ];
        }

        AuditLogService::log('export', 'guru', 'Export data guru ke XLSX: ' . count($rows) . ' data');

        return $excel->download(
            'data_guru_' . now()->format('Y-m-d') . '.xlsx',
            ['No', 'Nama', 'NIP', 'SK', 'SPMT', 'Status'],
            $rows,
            'Data Guru'
        );
    }
}
