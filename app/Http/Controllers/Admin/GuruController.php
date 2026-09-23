<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGuruRequest;
use App\Http\Requests\Admin\UpdateGuruRequest;
use App\Models\GuruProfile;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FileUploadService;
use App\Services\SpreadsheetExportService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GuruController extends Controller
{
    /** Kolom export/import — urutan awal = format data-guru-*.csv, lalu field lengkap. */
    private const EXPORT_COLUMNS = [
        'nama', 'nip', 'nipppk', 'nuptk', 'jenis_kelamin', 'agama',
        'tempat_lahir', 'tanggal_lahir', 'status_kepegawaian',
        'pangkat_golongan', 'jabatan', 'nik', 'alamat', 'no_hp',
        'npwp', 'email', 'status', 'tmt_golongan', 'tmt_cpns',
        'tmt_pns_pppk', 'tmt_sk_sekolah', 'aktif_ditampilkan',
        'no_akta_lahir', 'no_bpjs', 'profil_singkat',
        'social_instagram', 'social_facebook', 'social_twitter',
        'social_tiktok', 'social_youtube', 'social_linkedin',
        'social_website', 'social_github',
        'no_sk_kgb', 'tanggal_sk_kgb', 'gaji_pokok', 'mkg',
        'tmt_kgb_akhir', 'tmt_kgb_berikutnya',
    ];

    private const PROFILE_FIELDS = [
        'nipppk', 'nuptk', 'jenis_kelamin', 'agama', 'tempat_lahir',
        'tanggal_lahir', 'nik', 'status_kepegawaian', 'pangkat_golongan',
        'jabatan', 'tmt_golongan', 'tmt_cpns', 'tmt_pns_pppk',
        'tmt_sk_sekolah', 'alamat', 'no_hp', 'email', 'npwp',
        'no_akta_lahir', 'no_bpjs', 'profil_singkat',
        'social_instagram', 'social_facebook', 'social_twitter',
        'social_tiktok', 'social_youtube', 'social_linkedin',
        'social_website', 'social_github',
        'no_sk_kgb', 'tanggal_sk_kgb', 'gaji_pokok', 'mkg',
        'tmt_kgb_akhir', 'tmt_kgb_berikutnya',
    ];

    private const DATE_FIELDS = [
        'tanggal_lahir', 'tmt_golongan', 'tmt_cpns', 'tmt_pns_pppk',
        'tmt_sk_sekolah', 'tanggal_sk_kgb', 'tmt_kgb_akhir', 'tmt_kgb_berikutnya',
    ];

    public function __construct(protected FileUploadService $files)
    {
    }

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
        // Kumpulkan path file sebelum cascade delete agar tidak orphan di storage.
        $paths = $guru->attendances()
            ->get(['foto_masuk', 'foto_pulang', 'bukti_file', 'surat_tugas_file'])
            ->flatMap(fn ($a) => [$a->foto_masuk, $a->foto_pulang, $a->bukti_file, $a->surat_tugas_file])
            ->filter()->unique()->values();
        $guru->delete();
        foreach ($paths as $path) {
            $this->files->deleteFileIfOrphan($path);
        }

        AuditLogService::log('delete', 'guru', "Menghapus guru: {$name}");

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil dihapus.');
    }

    public function import()
    {
        request()->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
        ]);

        try {
            $rows = $this->readImportRows(request()->file('csv_file'));
        } catch (\Throwable $e) {
            return redirect()->route('admin.guru.index')
                ->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if ($rows === []) {
            return redirect()->route('admin.guru.index')
                ->with('error', 'File tidak berisi baris data.');
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $raw) {
            $data = $this->normalizeImportRow($raw);
            $name = trim((string) ($data['nama'] ?? ''));
            $username = $this->resolveUsername($data);

            if ($name === '' || $username === '') {
                $skipped++;
                continue;
            }

            $profileData = [];
            foreach (self::PROFILE_FIELDS as $field) {
                if (array_key_exists($field, $data)) {
                    $profileData[$field] = $data[$field];
                }
            }
            if (array_key_exists('aktif_ditampilkan', $data)) {
                $profileData['aktif_ditampilkan'] = filter_var($data['aktif_ditampilkan'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                    ?? (bool) $data['aktif_ditampilkan'];
            }

            $status = in_array($data['status'] ?? '', ['aktif', 'nonaktif'], true)
                ? $data['status']
                : 'aktif';

            $user = User::where('username', $username)->where('role', 'guru')->first();

            if ($user) {
                $user->update([
                    'name' => $name,
                    'status' => $status,
                ]);
                $profileData['nip'] = $username;
                $profileData['user_id'] = $user->id;
                GuruProfile::updateOrCreate(['user_id' => $user->id], $profileData);
                $updated++;
                continue;
            }

            $user = User::create([
                'name' => $name,
                'username' => $username,
                'password' => Hash::make('password'),
                'role' => 'guru',
                'status' => $status,
            ]);

            $profileData['user_id'] = $user->id;
            $profileData['nip'] = $username;
            GuruProfile::create($profileData);
            $imported++;
        }

        AuditLogService::log(
            'import',
            'guru',
            "Import guru: {$imported} baru, {$updated} diupdate, {$skipped} dilewati"
        );

        return redirect()->route('admin.guru.index')->with(
            'success',
            "Import selesai: {$imported} baru, {$updated} diupdate, {$skipped} dilewati."
        );
    }

    public function export(SpreadsheetExportService $excel)
    {
        $gurus = User::where('role', 'guru')->with('guruProfile')->orderBy('name')->get();

        $rows = [];
        foreach ($gurus as $guru) {
            $rows[] = $this->exportRow($guru);
        }

        AuditLogService::log('export', 'guru', 'Export data guru ke XLSX: ' . count($rows) . ' data');

        return $excel->download(
            'data_guru_' . now()->format('Y-m-d') . '.xlsx',
            self::EXPORT_COLUMNS,
            $rows,
            'Data Guru'
        );
    }

    private function exportRow(User $guru): array
    {
        $p = $guru->guruProfile;

        $map = [
            'nama' => $guru->name,
            'nip' => $guru->username,
            'status' => $guru->status === 'aktif' ? 'aktif' : 'nonaktif',
            'aktif_ditampilkan' => null,
            'gaji_pokok' => null,
        ];

        foreach (self::PROFILE_FIELDS as $field) {
            $map[$field] = $p ? $p->{$field} : null;
        }

        foreach (self::DATE_FIELDS as $field) {
            $val = $p ? $p->{$field} : null;
            if ($val instanceof \DateTimeInterface) {
                $map[$field] = $val->format('Y-m-d');
            } elseif ($val === null) {
                $map[$field] = null;
            } else {
                $map[$field] = (string) $val;
            }
        }

        if ($p) {
            $map['aktif_ditampilkan'] = $p->aktif_ditampilkan ? '1' : '0';
            $map['gaji_pokok'] = $p->gaji_pokok;
        }

        $row = [];
        foreach (self::EXPORT_COLUMNS as $col) {
            $v = $map[$col] ?? null;
            $row[] = $v === null ? '' : (string) $v;
        }

        return $row;
    }

    /** @return array<int,array<string,mixed>> */
    private function readImportRows($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        if (in_array($ext, ['csv', 'txt'], true)) {
            return $this->readCsvRows($file->getPathname());
        }

        if ($ext === 'xlsx') {
            return $this->readXlsxRows($file->getPathname());
        }

        // Fallback: coba deteksi dari mime/isi
        $info = @getimagesize($file->getPathname());
        unset($info);

        $head = file_get_contents($file->getPathname(), false, null, 0, 4);
        if (str_starts_with($head, "PK\x03\x04")) {
            return $this->readXlsxRows($file->getPathname());
        }

        return $this->readCsvRows($file->getPathname());
    }

    /** @return array<int,array<string,mixed>> */
    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Tidak bisa membuka file.');
        }

        // Deteksi delimiter (koma vs titik koma)
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = substr_count((string) $firstLine, ';') > substr_count((string) $firstLine, ',') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter, '"', '\\');
        if ($header === false) {
            fclose($handle);
            throw new \RuntimeException('Header tidak ditemukan.');
        }

        $keys = $this->mapHeaderKeys($header);
        $rows = [];

        while (($line = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            if ($line === null || $line === [null] || trim(implode('', array_map('strval', $line))) === '') {
                continue;
            }
            $rows[] = $this->combineHeader($keys, $line);
        }

        fclose($handle);

        return $rows;
    }

    /** @return array<int,array<string,mixed>> */
    private function readXlsxRows(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray(null, true, true, false);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if ($matrix === [] || count($matrix) < 2) {
            throw new \RuntimeException('Sheet kosong atau hanya berisi header.');
        }

        // Cari baris header (boleh ada kop/spasi di atas — hasil export kita)
        $headerIndex = null;
        foreach ($matrix as $i => $line) {
            $norm = array_map(
                fn ($c) => strtolower(trim(str_replace("\u{FEFF}", '', (string) $c))),
                (array) $line
            );
            if (in_array('nama', $norm, true) || in_array('name', $norm, true)) {
                $headerIndex = $i;
                break;
            }
        }

        if ($headerIndex === null) {
            // fallback: baris pertama yang punya >= 2 sel terisi
            foreach ($matrix as $i => $line) {
                $filled = array_filter((array) $line, fn ($c) => trim((string) $c) !== '');
                if (count($filled) >= 2) {
                    $headerIndex = $i;
                    break;
                }
            }
        }

        if ($headerIndex === null) {
            throw new \RuntimeException('Baris header tidak ditemukan (kolom "nama").');
        }

        $keys = $this->mapHeaderKeys($matrix[$headerIndex]);
        $rows = [];

        for ($i = $headerIndex + 1; $i < count($matrix); $i++) {
            $line = $matrix[$i];
            if ($line === null || trim(implode('', array_map(static fn ($v) => (string) $v, $line))) === '') {
                continue;
            }
            $rows[] = $this->combineHeader($keys, $line);
        }

        return $rows;
    }

    /** @return array<int,string|null> */
    private function mapHeaderKeys(array $header): array
    {
        return array_map(function ($cell) {
            $raw = trim(str_replace("\u{FEFF}", '', (string) $cell));
            $norm = strtolower(str_replace([' ', '-'], '_', $raw));

            $aliases = [
                'name' => 'nama',
                'username' => 'nip',
                'jk' => 'jenis_kelamin',
                'golongan' => 'pangkat_golongan',
                'tgl_lahir' => 'tanggal_lahir',
                'birth_date' => 'tanggal_lahir',
                'user_status' => 'status',
                'telp' => 'no_hp',
                'telepon' => 'no_hp',
                'no' => null,
                'no.' => null,
            ];

            if (array_key_exists($norm, $aliases)) {
                return $aliases[$norm];
            }

            if (array_key_exists($raw, $aliases)) {
                return $aliases[$raw];
            }

            return $norm;
        }, $header);
    }

    /** @return array<string,mixed> */
    private function combineHeader(array $keys, array $line): array
    {
        $out = [];
        foreach ($keys as $i => $key) {
            if ($key === null || $key === '') {
                continue;
            }
            $out[$key] = $line[$i] ?? null;
        }

        return $out;
    }

    /** @return array<string,mixed> */
    private function normalizeImportRow(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                $out[$key] = null;
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                // Excel numeric float → string tanpa .0
                if (preg_match('/^-?\d+\.0+$/', $value)) {
                    $value = explode('.', $value)[0];
                }
                $out[$key] = $value === '' ? null : $value;
                continue;
            }

            if (is_float($value) || is_int($value)) {
                $out[$key] = (string) $value;
                continue;
            }

            if ($value instanceof \DateTimeInterface) {
                $out[$key] = $value->format('Y-m-d');
                continue;
            }

            $out[$key] = $value;
        }

        // nama alias
        if (!isset($out['nama']) || $out['nama'] === null || $out['nama'] === '') {
            $out['nama'] = $out['name'] ?? null;
        }

        // jenis_kelamin → enum DB
        if (!empty($out['jenis_kelamin'])) {
            $jk = mb_strtolower(trim((string) $out['jenis_kelamin']));
            if (in_array($jk, ['laki-laki', 'laki laki', 'laki', 'male', 'm', 'pria'], true)) {
                $out['jenis_kelamin'] = 'laki-laki';
            } elseif (in_array($jk, ['perempuan', 'wanita', 'female', 'f'], true)) {
                $out['jenis_kelamin'] = 'perempuan';
            } else {
                $out['jenis_kelamin'] = null;
            }
        }

        // tanggal: Excel serial / dd/mm/yyyy / d-m-Y
        foreach (self::DATE_FIELDS as $field) {
            if (!empty($out[$field])) {
                $out[$field] = $this->normalizeDate($out[$field]);
            }
        }

        // status user
        if (!empty($out['status'])) {
            $st = mb_strtolower((string) $out['status']);
            if (in_array($st, ['aktif', 'active', 'ya', 'y', '1', 'true'], true)) {
                $out['status'] = 'aktif';
            } elseif (in_array($st, ['nonaktif', 'inactive', 'tidak', 'n', '0', 'false'], true)) {
                $out['status'] = 'nonaktif';
            } else {
                $out['status'] = 'aktif';
            }
        } else {
            $out['status'] = 'aktif';
        }

        // gaji_pokok numeric
        if (isset($out['gaji_pokok']) && $out['gaji_pokok'] !== null && $out['gaji_pokok'] !== '') {
            $gaji = str_replace(['.', ','], ['', ''], (string) $out['gaji_pokok']);
            // Jika pakai koma desimal gaya ID: 4.500.000,00 sudah ditangani; fallback:
            if (!is_numeric($out['gaji_pokok'])) {
                $out['gaji_pokok'] = is_numeric($gaji) ? $gaji : null;
            }
        }

        return $out;
    }

    private function normalizeDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $s = trim((string) $value);
        if ($s === '') {
            return null;
        }

        // Serial number Excel (hanya digit)
        if (preg_match('/^\d+(\.\d+)?$/', $s) && strlen($s) <= 5) {
            $ts = (new \DateTime('1899-12-30'))->setTimestamp(0);
            $days = (float) $s;
            if ($days > 0 && $days < 80000) {
                $ts = (new \DateTime('1899-12-30'))->modify('+' . (int) floor($days) . ' days');
                return $ts->format('Y-m-d');
            }
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'm/d/Y', 'Y/m/d'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $s);
            if ($dt instanceof \DateTime && $dt->format($fmt) === $s) {
                return $dt->format('Y-m-d');
            }
        }

        try {
            return (new \DateTime($s))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function resolveUsername(array $data): string
    {
        foreach (['nip', 'username', 'nipppk', 'nuptk', 'nik'] as $field) {
            $v = trim((string) ($data[$field] ?? ''));
            if ($v !== '') {
                // Buang spasi yang sering muncul di NIK contoh "3603 2716 ..."
                $v = preg_replace('/\s+/', '', $v);
                if (preg_match('/\d/', $v)) {
                    return $v;
                }
            }
        }

        return '';
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
