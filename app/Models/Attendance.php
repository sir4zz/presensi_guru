<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $casts = ['dinas_verified_at' => 'datetime'];
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

    public function dinasVerifier()
    {
        return $this->belongsTo(User::class, 'dinas_verified_by');
    }
}
