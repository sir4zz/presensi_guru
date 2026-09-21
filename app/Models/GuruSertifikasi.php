<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruSertifikasi extends Model
{
    protected $table = 'guru_sertifikasi';

    protected $fillable = [
        'guru_profile_id',
        'status',
        'no_sertifikat',
        'no_peserta',
        'no_nrg',
        'bidang_studi',
        'penyelenggara',
        'tahun_lulus',
    ];

    public function guruProfile()
    {
        return $this->belongsTo(GuruProfile::class);
    }
}
