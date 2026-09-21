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
use Illuminate\Support\Facades\Storage;

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

        $profileData = $this->extractProfileData($validated);
        $profileData['user_id'] = $user->id;
        $profileData['nip'] = $validated['nip'];

        $profile = GuruProfile::create($profileData);

        $this->syncNestedData($profile, $validated);

        AuditLogService::created('guru', $user, "Menambahkan guru baru: {$user->name}");

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function show($id)
    {
        $guru = User::where('role', 'guru')
            ->with(['guruProfile.pendidikan', 'guruProfile.tugas', 'guruProfile.sertifikasi', 'guruProfile.skPengangkatan'])
            ->findOrFail($id);
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
        $guru = User::where('role', 'guru')
            ->with(['guruProfile.pendidikan', 'guruProfile.tugas', 'guruProfile.sertifikasi', 'guruProfile.skPengangkatan'])
            ->findOrFail($id);

        if (request()->expectsJson() || request()->ajax()) {
            $html = view('admin.guru._form_tabs', ['guru' => $guru, 'mode' => 'edit'])->render();
            return response()->json(['html' => $html, 'guru' => $guru]);
        }

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

        $profileData = $this->extractProfileData($validated);
        $profileData['nip'] = $validated['nip'];

        $profile = GuruProfile::updateOrCreate(
            ['user_id' => $guru->id],
            $profileData
        );

        $this->syncNestedData($profile, $validated);

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

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $name = trim($data['name'] ?? $data['nama'] ?? '');
            $nip = trim($data['nip'] ?? $data['username'] ?? '');

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
                $guru->guruProfile?->nipppk ?? '-',
                $guru->guruProfile?->jabatan ?? '-',
                $guru->status === 'aktif' ? 'Aktif' : 'Nonaktif',
            ];
        }

        AuditLogService::log('export', 'guru', 'Export data guru ke XLSX: ' . count($rows) . ' data');

        return $excel->download(
            'data_guru_' . now()->format('Y-m-d') . '.xlsx',
            ['No', 'Nama', 'NIP', 'NIPPPK', 'Jabatan', 'Status'],
            $rows,
            'Data Guru'
        );
    }

    private function extractProfileData(array $validated): array
    {
        $fields = [
            'foto', 'nipppk', 'nuptk', 'jenis_kelamin', 'agama', 'tempat_lahir',
            'tanggal_lahir', 'nik', 'status_kepegawaian', 'pangkat_golongan', 'jabatan',
            'tmt_golongan', 'tmt_cpns', 'tmt_pns_pppk', 'tmt_sk_sekolah', 'aktif_ditampilkan',
            'alamat', 'no_hp', 'email', 'npwp', 'no_akta_lahir', 'no_bpjs', 'profil_singkat',
            'social_instagram', 'social_facebook', 'social_twitter', 'social_tiktok',
            'social_youtube', 'social_linkedin', 'social_website', 'social_github',
            'no_sk_kgb', 'tanggal_sk_kgb', 'gaji_pokok', 'mkg', 'tmt_kgb_akhir', 'tmt_kgb_berikutnya',
        ];

        $data = [];
        foreach ($fields as $field) {
            if (isset($validated[$field])) {
                $data[$field] = $validated[$field] ?: null;
            }
        }

        if (isset($validated['aktif_ditampilkan'])) {
            $data['aktif_ditampilkan'] = (bool) $validated['aktif_ditampilkan'];
        }

        return $data;
    }

    private function syncNestedData(GuruProfile $profile, array $validated): void
    {
        // Pendidikan
        if (isset($validated['pendidikan']) && is_array($validated['pendidikan'])) {
            $existingIds = $profile->pendidikan()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($validated['pendidikan'] as $pend) {
                if (!empty($pend['id'])) {
                    $submittedIds[] = $pend['id'];
                    $profile->pendidikan()->where('id', $pend['id'])->update([
                        'jenjang' => $pend['jenjang'] ?? null,
                        'jurusan' => $pend['jurusan'] ?? null,
                        'perguruan_tinggi' => $pend['perguruan_tinggi'] ?? null,
                        'tahun_lulus' => $pend['tahun_lulus'] ?? null,
                        'tempat' => $pend['tempat'] ?? null,
                        'nomor_ijazah' => $pend['nomor_ijazah'] ?? null,
                        'tanggal_ijazah' => $pend['tanggal_ijazah'] ?? null,
                    ]);
                } else {
                    $profile->pendidikan()->create([
                        'jenjang' => $pend['jenjang'] ?? null,
                        'jurusan' => $pend['jurusan'] ?? null,
                        'perguruan_tinggi' => $pend['perguruan_tinggi'] ?? null,
                        'tahun_lulus' => $pend['tahun_lulus'] ?? null,
                        'tempat' => $pend['tempat'] ?? null,
                        'nomor_ijazah' => $pend['nomor_ijazah'] ?? null,
                        'tanggal_ijazah' => $pend['tanggal_ijazah'] ?? null,
                    ]);
                }
            }

            $toDelete = array_diff($existingIds, $submittedIds);
            if (!empty($toDelete)) {
                $profile->pendidikan()->whereIn('id', $toDelete)->delete();
            }
        }

        // Tugas
        if (isset($validated['tugas']) && is_array($validated['tugas'])) {
            $existingIds = $profile->tugas()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($validated['tugas'] as $tugas) {
                if (!empty($tugas['id'])) {
                    $submittedIds[] = $tugas['id'];
                    $profile->tugas()->where('id', $tugas['id'])->update([
                        'jenis' => $tugas['jenis'] ?? null,
                        'uraian' => $tugas['uraian'] ?? null,
                        'jumlah_jam' => $tugas['jumlah_jam'] ?? null,
                    ]);
                } else {
                    $profile->tugas()->create([
                        'jenis' => $tugas['jenis'] ?? null,
                        'uraian' => $tugas['uraian'] ?? null,
                        'jumlah_jam' => $tugas['jumlah_jam'] ?? null,
                    ]);
                }
            }

            $toDelete = array_diff($existingIds, $submittedIds);
            if (!empty($toDelete)) {
                $profile->tugas()->whereIn('id', $toDelete)->delete();
            }
        }

        // Sertifikasi
        if (isset($validated['sertifikasi']) && is_array($validated['sertifikasi'])) {
            $existingIds = $profile->sertifikasi()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($validated['sertifikasi'] as $sert) {
                if (!empty($sert['id'])) {
                    $submittedIds[] = $sert['id'];
                    $profile->sertifikasi()->where('id', $sert['id'])->update([
                        'status' => $sert['status'] ?? null,
                        'no_sertifikat' => $sert['no_sertifikat'] ?? null,
                        'no_peserta' => $sert['no_peserta'] ?? null,
                        'no_nrg' => $sert['no_nrg'] ?? null,
                        'bidang_studi' => $sert['bidang_studi'] ?? null,
                        'penyelenggara' => $sert['penyelenggara'] ?? null,
                        'tahun_lulus' => $sert['tahun_lulus'] ?? null,
                    ]);
                } else {
                    $profile->sertifikasi()->create([
                        'status' => $sert['status'] ?? null,
                        'no_sertifikat' => $sert['no_sertifikat'] ?? null,
                        'no_peserta' => $sert['no_peserta'] ?? null,
                        'no_nrg' => $sert['no_nrg'] ?? null,
                        'bidang_studi' => $sert['bidang_studi'] ?? null,
                        'penyelenggara' => $sert['penyelenggara'] ?? null,
                        'tahun_lulus' => $sert['tahun_lulus'] ?? null,
                    ]);
                }
            }

            $toDelete = array_diff($existingIds, $submittedIds);
            if (!empty($toDelete)) {
                $profile->sertifikasi()->whereIn('id', $toDelete)->delete();
            }
        }

        // SK Pengangkatan
        if (isset($validated['sk_pengangkatan']) && is_array($validated['sk_pengangkatan'])) {
            $existingIds = $profile->skPengangkatan()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($validated['sk_pengangkatan'] as $sk) {
                if (!empty($sk['id'])) {
                    $submittedIds[] = $sk['id'];
                    $profile->skPengangkatan()->where('id', $sk['id'])->update([
                        'kategori' => $sk['kategori'] ?? null,
                        'nomor_sk' => $sk['nomor_sk'] ?? null,
                        'tanggal_sk' => $sk['tanggal_sk'] ?? null,
                        'pejabat' => $sk['pejabat'] ?? null,
                    ]);
                } else {
                    $profile->skPengangkatan()->create([
                        'kategori' => $sk['kategori'] ?? null,
                        'nomor_sk' => $sk['nomor_sk'] ?? null,
                        'tanggal_sk' => $sk['tanggal_sk'] ?? null,
                        'pejabat' => $sk['pejabat'] ?? null,
                    ]);
                }
            }

            $toDelete = array_diff($existingIds, $submittedIds);
            if (!empty($toDelete)) {
                $profile->skPengangkatan()->whereIn('id', $toDelete)->delete();
            }
        }
    }
}
