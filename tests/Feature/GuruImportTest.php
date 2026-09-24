<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GuruImportException;
use App\Services\GuruImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class GuruImportTest extends TestCase
{
    use RefreshDatabase;

    protected GuruImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GuruImportService;
    }

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin', 'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 'aktif',
        ]);
    }

    protected function realFile(string $name): UploadedFile
    {
        $path = base_path($name);
        $this->assertFileExists($path);
        return new UploadedFile($path, $name, null, null, true);
    }

    /** Buat file xlsx sintetis dari matriks baris (baris 1 = header). */
    protected function makeXlsx(array $rows): UploadedFile
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        foreach ($rows as $r => $cols) {
            foreach (array_values($cols) as $c => $val) {
                $sheet->setCellValue(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c + 1) . ($r + 1),
                    $val
                );
            }
        }
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        (new Xlsx($sheet->getParent()))->save($tmp);
        return new UploadedFile($tmp, 'sintetis.xlsx', null, null, true);
    }

    public function test_import_file_guru_asli(): void
    {
        $result = $this->service->import($this->realFile(
            'daftar-guru-SMKN 11 KABUPATEN TANGERANG-2026-08-19 12_38_54.xlsx'
        ));

        $this->assertSame(10, $result['imported']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame([], $result['errors']);

        // NIP 18 digit harus utuh (tidak rusak presisi float).
        $agus = User::where('username', '199108062025211119')->first();
        $this->assertNotNull($agus);
        $this->assertSame('Agus Abdul Gofur', $agus->name);
        $this->assertSame('aktif', $agus->status);
        $this->assertSame('199108062025211119', $agus->guruProfile->nip);
        $this->assertSame('4138769670130073', $agus->guruProfile->nuptk);
        $this->assertSame('laki-laki', $agus->guruProfile->jenis_kelamin);
        $this->assertSame('Guru', $agus->guruProfile->jabatan);
        $this->assertSame('1991-08-06', $agus->guruProfile->tanggal_lahir->format('Y-m-d'));
        $this->assertStringContainsString('cangkudu', $agus->guruProfile->alamat);
        $this->assertTrue(Hash::check('password', $agus->password));
    }

    public function test_import_file_tendik_asli(): void
    {
        $result = $this->service->import($this->realFile(
            'daftar-tendik-SMKN 11 KABUPATEN TANGERANG-2026-08-19 12_40_17.xlsx'
        ));

        $this->assertSame(4, $result['imported']);
        $endang = User::where('username', '196909142025211027')->first();
        $this->assertNotNull($endang);
        $this->assertSame('Tenaga Kependidikan', $endang->guruProfile->jabatan);
        $this->assertSame('guru', $endang->role);
    }

    public function test_import_ulang_semua_dilewati_tanpa_duplikat(): void
    {
        $file = 'daftar-guru-SMKN 11 KABUPATEN TANGERANG-2026-08-19 12_38_54.xlsx';
        $this->service->import($this->realFile($file));
        $result = $this->service->import($this->realFile($file));

        $this->assertSame(0, $result['imported']);
        $this->assertSame(10, $result['skipped']);
        $this->assertSame(10, User::where('role', 'guru')->count());
    }

    public function test_baris_rusak_dilaporkan_tanpa_menggagalkan_lainnya(): void
    {
        $file = $this->makeXlsx([
            ['Nama', 'NIP', 'JK', 'Tanggal Lahir', 'STATUS'],
            ['Valid Satu', '111', 'P', '2000-01-01', 'Aktif'],
            ['Tanpa NIP', '', 'L', '', ''],
            ['NIP Terlalu Panjang', '123456789012345678901', 'L', '', ''],
            ['Tanggal Rusak', '222', 'L', 'bukan-tanggal', 'Nonaktif'],
        ]);

        $result = $this->service->import($file);

        $this->assertSame(2, $result['imported']);
        $this->assertSame(2, $result['skipped']);
        $this->assertCount(2, $result['errors']);

        $perempuan = User::where('username', '111')->first();
        $this->assertSame('perempuan', $perempuan->guruProfile->jenis_kelamin);

        $nonaktif = User::where('username', '222')->first();
        $this->assertSame('nonaktif', $nonaktif->status);
        $this->assertNull($nonaktif->guruProfile->tanggal_lahir);
    }

    public function test_file_palsu_rename_xlsx_ditolak(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        file_put_contents($tmp, 'ini bukan excel');
        $file = new UploadedFile($tmp, 'palsu.xlsx', null, null, true);

        $this->expectException(GuruImportException::class);
        $this->service->import($file);
    }

    public function test_csv_lama_tetap_berfungsi(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'imp').'.csv';
        file_put_contents($tmp, "name,nip\nBudi Santoso,1987654321\nSiti Aminah,1987654322\n");
        $file = new UploadedFile($tmp, 'lama.csv', 'text/csv', null, true);

        $result = $this->service->import($file);

        $this->assertSame(2, $result['imported']);
        $this->assertNotNull(User::where('username', '1987654321')->first());
    }

    public function test_template_bisa_diunduh_dan_diimport_balik(): void
    {
        $response = $this->actingAs($this->makeAdmin())
            ->get(route('admin.guru.template'));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        // Roundtrip: template yang diunduh harus valid untuk diimport.
        $tmp = tempnam(sys_get_temp_dir(), 'tpl').'.xlsx';
        file_put_contents($tmp, $response->streamedContent());
        $result = $this->service->import(
            new UploadedFile($tmp, 'template.xlsx', null, null, true)
        );

        $this->assertSame(1, $result['imported']);
        $this->assertNotNull(User::where('username', '199005122025211001')->first());
    }
}
