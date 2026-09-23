<?php

namespace Tests\Feature;

use App\Services\FileUploadException;
use App\Services\FileUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadOptimizationTest extends TestCase
{
    protected FileUploadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new FileUploadService;
    }

    /** Buat file gambar asli (bukan fixture) berukuran besar. */
    protected function makeImageFile(int $w, int $h, string $format = 'jpeg'): UploadedFile
    {
        $img = imagecreatetruecolor($w, $h);
        // Gradasi agar kompresi realistis (tidak solid).
        for ($x = 0; $x < $w; $x += 10) {
            $c = imagecolorallocate($img, $x % 256, ($x * 2) % 256, ($x * 3) % 256);
            imagefilledrectangle($img, $x, 0, $x + 9, $h, $c);
        }
        $tmp = tempnam(sys_get_temp_dir(), 'upl').'.'.$format;
        match ($format) {
            'jpeg' => imagejpeg($img, $tmp, 95),
            'png' => imagepng($img, $tmp),
            'webp' => imagewebp($img, $tmp, 95),
        };
        imagedestroy($img);

        return new UploadedFile($tmp, "foto_kamera.{$format}", null, null, true);
    }

    public function test_selfie_besar_di_resize_dan_jadi_webp_dengan_thumbnail(): void
    {
        $file = $this->makeImageFile(3000, 2000, 'jpeg');
        $origBytes = filesize($file->getPathname());

        $path = $this->service->storeSelfie($file);

        // Nama random, folder tanggal, ekstensi webp.
        $this->assertMatchesRegularExpression(
            '#^attendance/selfie/\d{4}/\d{2}/\d{2}/[0-9a-f]{32}\.webp$#',
            $path
        );
        $this->assertStringNotContainsString('foto_kamera', $path);

        $info = getimagesize(Storage::disk('public')->path($path));
        $this->assertLessThanOrEqual(1280, max($info[0], $info[1]));
        $this->assertSame('image/webp', $info['mime']);
        $this->assertLessThan($origBytes, Storage::disk('public')->size($path));

        // Thumbnail ada dan kecil.
        $thumb = dirname($path).'/thumbs/'.basename($path);
        $this->assertTrue(Storage::disk('public')->exists($thumb));
        $thumbInfo = getimagesize(Storage::disk('public')->path($thumb));
        $this->assertLessThanOrEqual(320, max($thumbInfo[0], $thumbInfo[1]));

        $this->assertSame($thumb, FileUploadService::thumbPath($path));
    }

    public function test_selfie_png_dikonversi_tanpa_menyimpan_original(): void
    {
        $path = $this->service->storeSelfie($this->makeImageFile(800, 600, 'png'));

        $this->assertStringEndsWith('.webp', $path);
        // Tidak ada file png original yang tersisa.
        $this->assertSame([], glob(Storage::disk('public')->path('attendance/selfie/*/*/*/*.png') ?: []));
    }

    public function test_file_palsu_ekstensi_gambar_ditolak(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upl').'.jpg';
        file_put_contents($tmp, '<?php echo "hacked"; ?>');
        $file = new UploadedFile($tmp, 'selfie.jpg', 'image/jpeg', null, true);

        $this->expectException(FileUploadException::class);
        $this->service->storeSelfie($file);
    }

    public function test_executable_ditolak_sebagai_lampiran(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upl').'.pdf';
        file_put_contents($tmp, '%PDF-1.4 fake /JavaScript (alert)');
        $file = new UploadedFile($tmp, 'dokumen.pdf', 'application/pdf', null, true);

        $this->expectException(FileUploadException::class);
        $this->service->storeEvidence($file);
    }

    public function test_lampiran_pdf_tetap_pdf_dan_tervalidasi(): void
    {
        // PDF minimal yang valid.
        $tmp = tempnam(sys_get_temp_dir(), 'upl').'.pdf';
        file_put_contents($tmp, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");
        $file = new UploadedFile($tmp, 'surat tugas (1).pdf', 'application/pdf', null, true);

        $path = $this->service->storeEvidence($file);

        $this->assertMatchesRegularExpression(
            '#^attendance/evidence/\d{4}/\d{2}/\d{2}/[0-9a-f]{32}\.pdf$#',
            $path
        );
        $this->assertSame('%PDF-', file_get_contents(Storage::disk('public')->path($path), false, null, 0, 5));
        // PDF tidak punya thumbnail — thumb() return path asli.
        $this->assertSame($path, FileUploadService::thumbPath($path));
    }

    public function test_file_bukan_pdf_dengan_ekstensi_pdf_ditolak(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'upl').'.pdf';
        file_put_contents($tmp, 'ini bukan pdf sama sekali');
        $file = new UploadedFile($tmp, 'surat.pdf', 'application/pdf', null, true);

        $this->expectException(FileUploadException::class);
        $this->service->storeEvidence($file);
    }

    public function test_file_lama_tanpa_thumbnail_fallback_ke_original(): void
    {
        // Simulasi file lama era JPG tanpa folder thumbs/.
        Storage::disk('public')->put('attendance/selfie/selfie_2_123_jpg.jpg', 'fake');
        $this->assertSame(
            'attendance/selfie/selfie_2_123_jpg.jpg',
            FileUploadService::thumbPath('attendance/selfie/selfie_2_123_jpg.jpg')
        );
    }

    public function test_delete_menghapus_file_dan_thumbnail(): void
    {
        $path = $this->service->storeSelfie($this->makeImageFile(800, 600, 'jpeg'));
        $thumb = dirname($path).'/thumbs/'.basename($path);
        $this->assertTrue(Storage::disk('public')->exists($thumb));

        $this->service->deleteFile($path);

        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertFalse(Storage::disk('public')->exists($thumb));
    }
}
