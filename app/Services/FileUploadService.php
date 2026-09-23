<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Layanan terpusat untuk upload file yang aman dan efisien.
 *
 * Dipakai bersama oleh foto absensi (selfie), lampiran izin/dinas luar,
 * dan logo sekolah agar tidak ada duplicate upload-processing logic.
 *
 * Gambar: resize sisi terpanjang, konversi ke WebP (fallback JPEG),
 * koreksi orientasi EXIF, nama file random, folder per tanggal,
 * plus thumbnail sekali saat upload (bukan per request).
 *
 * PDF: tetap PDF, hanya validasi MIME/content + nama random.
 *
 * Database hanya menyimpan path relatif (string), bukan binary.
 * File lama (JPG/PNG/PDF) tetap kompatibel: thumb() fallback ke
 * path original bila thumbnail belum ada.
 */
class FileUploadService
{
    public const SELFIE_MAX_SIDE = 1280;

    public const SELFIE_THUMB_SIDE = 320;

    public const SELFIE_QUALITY = 80;

    /** Lampiran gambar dibuat sedikit lebih besar agar tulisan dokumen tetap terbaca. */
    public const EVIDENCE_MAX_SIDE = 1600;

    public const EVIDENCE_THUMB_SIDE = 320;

    public const EVIDENCE_QUALITY = 80;

    public const LOGO_MAX_SIDE = 512;

    public const LOGO_QUALITY = 85;

    public const PDF_MAX_BYTES = 5 * 1024 * 1024;

    public const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    public const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Simpan foto selfie absensi. Return path relatif disk public.
     *
     * @throws FileUploadException (pesan aman untuk user, detail di log)
     */
    public function storeSelfie(UploadedFile $file): string
    {
        return $this->storeImage(
            $file,
            'attendance/selfie',
            self::SELFIE_MAX_SIDE,
            self::SELFIE_QUALITY,
            self::SELFIE_THUMB_SIDE
        );
    }

    /**
     * Simpan lampiran izin/dinas luar (gambar dioptimasi, PDF tetap PDF).
     * Return path relatif disk public.
     *
     * @throws FileUploadException
     */
    public function storeEvidence(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'pdf') {
            return $this->storePdf($file, 'attendance/evidence');
        }

        if (! in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            throw new FileUploadException('Format lampiran harus JPG, PNG, WebP, atau PDF.');
        }

        return $this->storeImage(
            $file,
            'attendance/evidence',
            self::EVIDENCE_MAX_SIDE,
            self::EVIDENCE_QUALITY,
            self::EVIDENCE_THUMB_SIDE
        );
    }

    /** Simpan logo sekolah (kecil, transparansi dipertahankan). */
    public function storeLogo(UploadedFile $file): string
    {
        return $this->storeImage($file, 'logos', self::LOGO_MAX_SIDE, self::LOGO_QUALITY, 0);
    }

    /**
     * Path thumbnail untuk sebuah path file gambar, atau path original
     * bila thumbnail belum ada (file lama) / file bukan gambar (PDF).
     */
    public static function thumbPath(?string $path): ?string
    {
        if (! $path || self::isPdfPath($path)) {
            return $path;
        }

        $thumb = \dirname($path).'/thumbs/'.\basename($path);

        try {
            if (Storage::disk('public')->exists($thumb)) {
                return $thumb;
            }
        } catch (\Throwable) {
            // Storage tidak tersedia (mis. saat testing ringan) — fallback original.
        }

        return $path;
    }

    /** URL publik thumbnail (fallback ke file original untuk file lama). */
    public static function thumbUrl(?string $path): ?string
    {
        $resolved = self::thumbPath($path);

        return $resolved ? Storage::url($resolved) : null;
    }

    /** URL publik file original. */
    public static function fileUrl(?string $path): ?string
    {
        return $path ? Storage::url($path) : null;
    }

    public static function isPdfPath(?string $path): bool
    {
        return $path !== null && strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Hapus file beserta thumbnail-nya (jika ada). Aman dipanggil
     * dengan path null / file yang sudah tidak ada.
     */
    public function deleteFile(?string $path): void
    {
        if (! $path || str_contains($path, '..')) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($path);

        if (! self::isPdfPath($path)) {
            $disk->delete(\dirname($path).'/thumbs/'.\basename($path));
        }
    }

    /**
     * Hapus file hanya jika tidak direferensikan record attendance lain.
     * Dipakai untuk lampiran yang dishare banyak record (izin massal).
     */
    public function deleteFileIfOrphan(?string $path, ?int $exceptAttendanceId = null): void
    {
        if (! $path) {
            return;
        }

        $query = \App\Models\Attendance::where(function ($q) use ($path) {
            $q->where('bukti_file', $path)->orWhere('surat_tugas_file', $path);
        });

        if ($exceptAttendanceId) {
            $query->where('id', '!=', $exceptAttendanceId);
        }

        if (! $query->exists()) {
            $this->deleteFile($path);
        }
    }

    // ------------------------------------------------------------------
    // Internal
    // ------------------------------------------------------------------

    protected function storeImage(
        UploadedFile $file,
        string $baseDir,
        int $maxSide,
        int $quality,
        int $thumbSide
    ): string {
        $tmpPath = $file->getPathname();
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS, true)) {
            throw new FileUploadException('Format gambar harus JPG, PNG, atau WebP.');
        }

        // Validasi content sebenarnya — jangan percaya $_FILES['type']/ekstensi.
        $size = @getimagesize($tmpPath);
        if ($size === false) {
            Log::warning('Upload gambar ditolak: bukan gambar valid.', ['name' => $file->getClientOriginalName()]);
            throw new FileUploadException('File bukan gambar yang valid.');
        }

        $realMime = function_exists('finfo_open')
            ? @((new \finfo(FILEINFO_MIME_TYPE))->file($tmpPath))
            : $file->getMimeType();

        if (! in_array($realMime, self::ALLOWED_IMAGE_MIMES, true) || ! in_array($size['mime'] ?? '', self::ALLOWED_IMAGE_MIMES, true)) {
            Log::warning('Upload gambar ditolak: MIME tidak valid.', ['mime' => $realMime, 'size_mime' => $size['mime'] ?? null]);
            throw new FileUploadException('File bukan gambar yang valid.');
        }

        $image = $this->createImageResource($tmpPath, $realMime);
        if ($image === false) {
            Log::warning('Upload gambar ditolak: gagal dibaca image library.');
            throw new FileUploadException('File gambar tidak dapat diproses.');
        }

        $written = [];

        try {
            $image = $this->applyExifOrientation($tmpPath, $image, $realMime);
            $image = $this->constrainSize($image, $maxSide);

            $useWebp = function_exists('imagewebp');
            $targetExt = $useWebp ? 'webp' : 'jpg';

            $dateDir = $baseDir.'/'.now()->format('Y/m/d');
            $name = bin2hex(random_bytes(16)).'.'.$targetExt;
            $path = $dateDir.'/'.$name;

            $disk = Storage::disk('public');
            $disk->makeDirectory($dateDir);

            $encoded = $this->encodeImage($image, $targetExt, $quality);
            $disk->put($path, $encoded);
            $written[] = $path;

            // Thumbnail dibuat SEKALI saat upload, bukan per request.
            if ($thumbSide > 0) {
                $thumbDir = $dateDir.'/thumbs';
                $disk->makeDirectory($thumbDir);
                $thumbImg = $this->constrainSize($image, $thumbSide);
                $disk->put($thumbDir.'/'.$name, $this->encodeImage($thumbImg, $targetExt, $quality));
                if ($thumbImg !== $image) {
                    imagedestroy($thumbImg);
                }
            }

            return $path;
        } catch (\Throwable $e) {
            foreach ($written as $p) {
                Storage::disk('public')->delete($p);
            }
            Log::error('Optimasi gambar gagal.', ['error' => $e->getMessage()]);
            throw new FileUploadException('Gagal memproses gambar. Silakan coba lagi.');
        } finally {
            if (isset($image) && (is_resource($image) || $image instanceof \GdImage)) {
                imagedestroy($image);
            }
        }
    }

    protected function storePdf(UploadedFile $file, string $baseDir): string
    {
        $tmpPath = $file->getPathname();

        if ($file->getSize() > self::PDF_MAX_BYTES) {
            throw new FileUploadException('Ukuran PDF maksimal 5 MB.');
        }

        $realMime = function_exists('finfo_open')
            ? @((new \finfo(FILEINFO_MIME_TYPE))->file($tmpPath))
            : $file->getMimeType();

        if (! in_array($realMime, ['application/pdf', 'application/x-pdf'], true)) {
            Log::warning('Upload PDF ditolak: MIME tidak valid.', ['mime' => $realMime]);
            throw new FileUploadException('File bukan PDF yang valid.');
        }

        // Verifikasi magic bytes — file .pdf palsu (mis. script) ditolak.
        $handle = @fopen($tmpPath, 'rb');
        $header = $handle ? fread($handle, 5) : '';
        if ($handle) {
            fclose($handle);
        }
        if ($header !== '%PDF-') {
            Log::warning('Upload PDF ditolak: header bukan %PDF-.');
            throw new FileUploadException('File bukan PDF yang valid.');
        }

        // Tolak PDF yang menyisipkan JavaScript/aksi otomatis (vektor umum malware PDF).
        $sample = @file_get_contents($tmpPath, false, null, 0, 200000);
        if (is_string($sample) && preg_match('/\/(JavaScript|JS|Launch|EmbeddedFile|OpenAction)\b/i', $sample)) {
            Log::warning('Upload PDF ditolak: mengandung script/aksi otomatis.');
            throw new FileUploadException('PDF mengandung konten yang tidak diizinkan.');
        }

        $dateDir = $baseDir.'/'.now()->format('Y/m/d');
        $name = bin2hex(random_bytes(16)).'.pdf';
        $path = $dateDir.'/'.$name;

        $disk = Storage::disk('public');
        $disk->makeDirectory($dateDir);

        // Stream ke disk tanpa memuat seluruh file ke memory sekaligus.
        $stream = fopen($tmpPath, 'rb');
        if (! $stream) {
            throw new FileUploadException('Gagal membaca file PDF.');
        }
        try {
            $disk->put($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }

    protected function createImageResource(string $tmpPath, string $mime): \GdImage|false
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmpPath),
            'image/png' => @imagecreatefrompng($tmpPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpPath) : false,
            default => false,
        };
    }

    /** Koreksi orientasi foto kamera HP berdasarkan EXIF (hanya JPEG). */
    protected function applyExifOrientation(string $tmpPath, \GdImage $image, string $mime): \GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($tmpPath);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        if ($orientation < 2 || $orientation > 8) {
            return $image;
        }

        // Normalisasi ke truecolor + pertahankan alpha channel.
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
        if ($rotated !== $image) {
            imagedestroy($image);
            $image = $rotated;
        }

        if (in_array($orientation, [2, 4, 5, 7], true) && function_exists('imageflip')) {
            imageflip($image, in_array($orientation, [2, 5], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        return $image;
    }

    /** Kecilkan sisi terpanjang ke $maxSide; biarkan jika sudah cukup kecil. */
    protected function constrainSize(\GdImage $image, int $maxSide): \GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $longest = max($w, $h);

        if ($longest <= $maxSide) {
            return $image;
        }

        $scale = $maxSide / $longest;
        $scaled = imagescale($image, (int) round($w * $scale), (int) round($h * $scale));

        return $scaled === false ? $image : $scaled;
    }

    protected function encodeImage(\GdImage $image, string $targetExt, int $quality): string
    {
        // Pertahankan transparansi PNG/WebP.
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        try {
            if ($targetExt === 'webp') {
                imagewebp($image, null, $quality);
            } else {
                // Fallback JPEG: flatten dengan latar putih.
                $w = imagesx($image);
                $h = imagesy($image);
                $flat = imagecreatetruecolor($w, $h);
                $white = imagecolorallocate($flat, 255, 255, 255);
                imagefill($flat, 0, 0, $white);
                imagecopy($flat, $image, 0, 0, 0, 0, $w, $h);
                imagejpeg($flat, null, min(95, $quality + 2));
                imagedestroy($flat);
            }

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
