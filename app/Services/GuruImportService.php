<?php

namespace App\Services;

use App\Models\GuruProfile;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Import data guru/tendik dari file Excel Dapodik (.xlsx/.xls)
 * atau CSV lama, ke tabel users + guru_profiles.
 *
 * - Kolom dipetakan BERDASARKAN NAMA HEADER (case-insensitive),
 *   bukan posisi — tahan terhadap judul 4 baris & urutan acak.
 * - NIP/NUPTK/NIK dibaca sebagai string digit mentah (raw value),
 *   TIDAK via getFormattedValue() yang merusak presisi 16-18 digit.
 * - Tanggal mendukung string (YYYY-MM-DD) dan serial Excel.
 */
class GuruImportService
{
    /** Header kanonis untuk template + pencocokan (urutan = urutan template). */
    public const TEMPLATE_HEADERS = [
        'No', 'Nama', 'NUPTK', 'JK', 'Tempat Lahir', 'Tanggal Lahir',
        'NIP', 'Status Kepegawaian', 'Jenis PTK', 'Agama',
        'Alamat Jalan', 'RT', 'RW', 'Nama Dusun', 'Desa/Kelurahan',
        'Kecamatan', 'Kode Pos', 'Telepon', 'HP', 'Email',
        'Pangkat Golongan', 'TMT Pengangkatan', 'NIK', 'NPWP', 'STATUS',
    ];

    /** Alias header dinormalisasi (lowercase, tanpa spasi ganda) => key internal. */
    protected const HEADER_MAP = [
        'no' => 'no',
        'nama' => 'nama', 'name' => 'nama',
        'nuptk' => 'nuptk',
        'jk' => 'jk', 'jenis kelamin' => 'jk', 'l/p' => 'jk',
        'tempat lahir' => 'tempat_lahir',
        'tanggal lahir' => 'tanggal_lahir', 'tgl lahir' => 'tanggal_lahir',
        'nip' => 'nip',
        'status kepegawaian' => 'status_kepegawaian',
        'jenis ptk' => 'jenis_ptk',
        'agama' => 'agama',
        'alamat jalan' => 'alamat_jalan', 'alamat' => 'alamat_jalan',
        'rt' => 'rt', 'rw' => 'rw',
        'nama dusun' => 'dusun', 'dusun' => 'dusun',
        'desa/kelurahan' => 'desa', 'desa' => 'desa', 'kelurahan' => 'desa',
        'kecamatan' => 'kecamatan',
        'kode pos' => 'kode_pos', 'kodepos' => 'kode_pos',
        'telepon' => 'telepon', 'telp' => 'telepon',
        'hp' => 'hp', 'no hp' => 'hp', 'handphone' => 'hp', 'no. hp' => 'hp',
        'email' => 'email',
        'pangkat golongan' => 'pangkat_golongan', 'pangkat/gol' => 'pangkat_golongan',
        'tmt pengangkatan' => 'tmt_pengangkatan',
        'nik' => 'nik',
        'npwp' => 'npwp',
        'status' => 'status',
    ];

    /**
     * @return array{imported:int,skipped:int,errors:array<int,string>}
     *
     * @throws GuruImportException bila file tidak dapat dibaca.
     */
    public function import(UploadedFile $file, ?int $actorId = null): array
    {
        $actorId ??= auth()->id();

        $extension = strtolower($file->getClientOriginalExtension());
        $extension = strtolower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'xlsx', 'xls' => $this->parseSpreadsheet($file),
            'csv', 'txt' => $this->parseCsv($file),
            default => throw new GuruImportException('Format file harus XLSX, XLS, atau CSV.'),
        };

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $lineNo = $index + 1;
            try {
                $result = $this->importRow($row);
                if ($result) {
                    $imported++;
                } else {
                    $skipped++;
                }
            } catch (GuruImportRowException $e) {
                $skipped++;
                if (count($errors) < 20) {
                    $errors[] = "Baris {$lineNo}: {$e->getMessage()}";
                }
            }
        }

        if ($actorId) {
            AuditLog::create([
                'user_id' => $actorId,
                'action' => 'import',
                'module' => 'guru',
                'description' => "Import data guru: {$imported} berhasil, {$skipped} dilewati.",
                'new_data' => ['imported' => $imported, 'skipped' => $skipped, 'file' => $file->getClientOriginalName()],
            ]);
        }

        return compact('imported', 'skipped', 'errors');
    }

    /** Contoh baris untuk template Excel. */
    public function templateExampleRow(): array
    {
        return [
            1, 'Budi Santoso', '1234567890123456', 'L', 'Tangerang', '1990-05-12',
            '199005122025211001', 'PPPK Paruh Waktu', 'Guru', 'Islam',
            'Kp. Sukamaju', '003', '001', 'Sukamaju', 'Jayanti',
            'Jayanti', '15634', '', '081234567890', 'budi@example.com',
            '', '', '3603011205900001', '', 'Aktif',
        ];
    }

    // ------------------------------------------------------------------
    // Parsing
    // ------------------------------------------------------------------

    /** @return array<int,array<string,string>> */
    protected function parseSpreadsheet(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
        } catch (\Throwable $e) {
            Log::warning('Import guru ditolak: file Excel tidak valid.', ['error' => $e->getMessage()]);
            throw new GuruImportException('File Excel tidak dapat dibaca. Pastikan format .xlsx/.xls yang valid.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = min($sheet->getHighestRow(), 5005); // batas aman
        $highestCol = min(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn()), 60);

        // Cari baris header (berisi "Nama" + "NIP"), maksimal 10 baris pertama.
        $headerRow = null;
        $colMap = [];
        for ($r = 1; $r <= min(10, $highestRow); $r++) {
            $map = $this->detectHeaderRow($sheet, $r, $highestCol);
            if ($map !== null) {
                $headerRow = $r;
                $colMap = $map;
                break;
            }
        }

        if ($headerRow === null) {
            throw new GuruImportException('Header kolom (Nama, NIP, ...) tidak ditemukan. Gunakan template yang disediakan.');
        }

        $rows = [];
        for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
            $row = [];
            $isEmpty = true;
            foreach ($colMap as $key => $c) {
                $val = $this->cellString($sheet->getCell([$c, $r]));
                $row[$key] = $val;
                if ($val !== '') {
                    $isEmpty = false;
                }
            }
            if (! $isEmpty) {
                $rows[] = $row;
            }
        }

        $spreadsheet->disconnectWorksheets();

        return $rows;
    }

    /** @return array<string,int>|null */
    protected function detectHeaderRow($sheet, int $row, int $highestCol): ?array
    {
        $map = [];
        for ($c = 1; $c <= $highestCol; $c++) {
            $norm = $this->normalizeHeader($this->cellString($sheet->getCell([$c, $row])));
            if ($norm !== '' && isset(self::HEADER_MAP[$norm]) && ! isset($map[self::HEADER_MAP[$norm]])) {
                $map[self::HEADER_MAP[$norm]] = $c;
            }
        }

        return isset($map['nama'], $map['nip']) ? $map : null;
    }

    protected function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        return (string) preg_replace('/\s+/', ' ', $value);
    }

    /**
     * Baca sel sebagai string TANPA merusak digit panjang.
     * getFormattedValue() dilarang untuk angka: NIP 18 digit
     * '199108062025211119' rusak menjadi '...11104'.
     */
    protected function cellString(Cell $cell): string
    {
        $value = $cell->getValue();

        if ($value === null || $value === '') {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            // Serial tanggal Excel → ISO; angka bulat → tanpa desimal/notasi ilmiah.
            if (ExcelDate::isDateTime($cell)) {
                try {
                    return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
                } catch (\Throwable) {
                    return '';
                }
            }
            if (floor($value) == $value && abs($value) < 1e15) {
                return number_format($value, 0, '', '');
            }
            // Di atas 1e15 presisi float hilang — kembalikan apa adanya.
            return rtrim(rtrim(sprintf('%.0f', $value), '0'), '.');
        }
        if (is_string($value)) {
            // String numerik raksasa (tipe 's') dipertahankan utuh.
            return trim($value);
        }

        return trim((string) $cell->getFormattedValue());
    }

    /** @return array<int,array<string,string>> */
    protected function parseCsv(UploadedFile $file): array
    {
        $handle = fopen($file->getPathname(), 'r');
        if (! $handle) {
            throw new GuruImportException('File CSV tidak dapat dibaca.');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new GuruImportException('File CSV kosong.');
        }

        $colMap = [];
        foreach ($header as $i => $name) {
            $norm = $this->normalizeHeader((string) $name);
            if ($norm !== '' && isset(self::HEADER_MAP[$norm])) {
                $colMap[self::HEADER_MAP[$norm]] = $i;
            }
        }

        if (! isset($colMap['nama']) || ! isset($colMap['nip'])) {
            // Format CSV lama: name,nip tanpa header dikenali? Coba posisi 0,1.
            fclose($handle);
            throw new GuruImportException('Header CSV harus memuat kolom Nama dan NIP.');
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            $row = [];
            $isEmpty = true;
            foreach ($colMap as $key => $i) {
                $val = trim((string) ($data[$i] ?? ''));
                // Hilangkan BOM di sel pertama bila ada.
                $val = ltrim($val, "\xEF\xBB\xBF");
                $row[$key] = $val;
                if ($val !== '') {
                    $isEmpty = false;
                }
            }
            if (! $isEmpty) {
                $rows[] = $row;
            }
        }
        fclose($handle);

        return $rows;
    }

    // ------------------------------------------------------------------
    // Import per baris — return true bila dibuat, false bila dilewati.
    // ------------------------------------------------------------------

    protected function importRow(array $row): bool
    {
        $name = trim($row['nama'] ?? '');
        $nip = $this->digitsOnly($row['nip'] ?? '');

        if ($name === '' || $nip === '') {
            throw new GuruImportRowException('Nama/NIP kosong.');
        }
        if (strlen($nip) > 20) {
            throw new GuruImportRowException("NIP '{$nip}' melebihi 20 digit.");
        }
        if (User::where('username', $nip)->exists()) {
            return false; // duplikat — lewati tanpa error
        }

        $alamat = $this->joinAddress($row);
        $status = $this->normalizeStatus($row['status'] ?? '');

        DB::transaction(function () use ($row, $name, $nip, $alamat, $status) {
            $user = User::create([
                'name' => $name,
                'username' => $nip,
                'password' => Hash::make('password'),
                'role' => 'guru',
                'status' => $status,
            ]);

            GuruProfile::create([
                'user_id' => $user->id,
                'nip' => $nip,
                'nuptk' => $this->orNull($row['nuptk'] ?? ''),
                'jenis_kelamin' => $this->normalizeGender($row['jk'] ?? ''),
                'agama' => $this->orNull($row['agama'] ?? ''),
                'tempat_lahir' => $this->orNull($row['tempat_lahir'] ?? ''),
                'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? ''),
                'nik' => $this->orNull($row['nik'] ?? ''),
                'status_kepegawaian' => $this->orNull($row['status_kepegawaian'] ?? ''),
                'pangkat_golongan' => $this->orNull($row['pangkat_golongan'] ?? ''),
                'jabatan' => $this->orNull($row['jenis_ptk'] ?? ''),
                'tmt_sk_sekolah' => $this->parseDate($row['tmt_pengangkatan'] ?? ''),
                'alamat' => $alamat,
                'no_hp' => $this->orNull($row['hp'] ?? '') ?? $this->orNull($row['telepon'] ?? ''),
                'email' => $this->orNull($row['email'] ?? ''),
                'npwp' => $this->orNull($row['npwp'] ?? ''),
            ]);
        });

        return true;
    }

    protected function digitsOnly(string $value): string
    {
        return (string) preg_replace('/\D+/', '', trim($value));
    }

    protected function orNull(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    protected function normalizeGender(string $value): ?string
    {
        $v = mb_strtolower(trim($value));
        if (in_array($v, ['l', 'laki', 'laki-laki', 'lakilaki', 'male'], true)) {
            return 'laki-laki';
        }
        if (in_array($v, ['p', 'perempuan', 'pr', 'female'], true)) {
            return 'perempuan';
        }
        return null;
    }

    protected function normalizeStatus(string $value): string
    {
        $v = mb_strtolower(trim($value));
        if (in_array($v, ['nonaktif', 'non aktif', 'tidak aktif', 'keluar', 'berhenti'], true)) {
            return 'nonaktif';
        }
        return 'aktif';
    }

    protected function joinAddress(array $row): ?string
    {
        $parts = [];
        foreach (['alamat_jalan', 'rt', 'rw', 'dusun', 'desa', 'kecamatan', 'kode_pos'] as $key) {
            $val = trim($row[$key] ?? '');
            if ($val === '') {
                continue;
            }
            if ($key === 'rt') {
                $val = 'RT ' . $val;
            } elseif ($key === 'rw') {
                $val = 'RW ' . $val;
            }
            $parts[] = $val;
        }
        return $parts === [] ? null : implode(', ', $parts);
    }

    protected function parseDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        // Serial Excel sebagai string angka.
        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 80000) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null; // tanggal opsional rusak → null, baris tetap diimport
        }
    }
}
