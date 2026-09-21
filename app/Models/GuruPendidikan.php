<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruPendidikan extends Model
{
    protected $table = 'guru_pendidikan';

    protected $fillable = [
        'guru_profile_id',
        'jenjang',
        'jurusan',
        'perguruan_tinggi',
        'tahun_lulus',
        'tempat',
        'nomor_ijazah',
        'tanggal_ijazah',
    ];

    protected $casts = [
        'tanggal_ijazah' => 'date',
    ];

    public function guruProfile()
    {
        return $this->belongsTo(GuruProfile::class);
    }
}
