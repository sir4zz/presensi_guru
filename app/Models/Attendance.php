<?php

namespace App\Models;

use App\Services\FileUploadService;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $casts = ['dinas_verified_at' => 'datetime'];

    /** Thumb path ikut terserialisasi agar daftar/modal bisa memuat thumbnail kecil. */
    protected $appends = ['foto_masuk_thumb', 'foto_pulang_thumb', 'bukti_thumb'];
    protected $fillable = [
        'guru_id',
        'tanggal',
        'status',
        'jam_masuk',
        'jam_pulang',
        'foto_masuk',
        'foto_pulang',
        'lat_masuk',
        'lng_masuk',
        'distance_masuk',
        'accuracy_masuk',
        'lat_pulang',
        'lng_pulang',
        'distance_pulang',
        'accuracy_pulang',
        'keterangan',
        'bukti_file',
        'keperluan_dinas',
        'lokasi_dinas',
        'surat_tugas_file',
        'dinas_verified_by',
        'dinas_verified_at',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Thumbnail foto masuk (fallback ke file original untuk file lama
     * yang belum punya thumbnail). Dipakai di daftar/kalender agar
     * browser tidak memuat gambar full-size sebagai thumbnail.
     */
    public function getFotoMasukThumbAttribute(): ?string
    {
        return FileUploadService::thumbPath($this->foto_masuk);
    }

    public function getFotoPulangThumbAttribute(): ?string
    {
        return FileUploadService::thumbPath($this->foto_pulang);
    }

    public function getBuktiThumbAttribute(): ?string
    {
        return FileUploadService::thumbPath($this->bukti_file);
    }

    public function dinasVerifier()
    {
        return $this->belongsTo(User::class, 'dinas_verified_by');
    }
}
