<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruSkPengangkatan extends Model
{
    protected $table = 'guru_sk_pengangkatan';

    protected $fillable = [
        'guru_profile_id',
        'kategori',
        'nomor_sk',
        'tanggal_sk',
        'pejabat',
    ];

    protected $casts = [
        'tanggal_sk' => 'date',
    ];

    public function guruProfile()
    {
        return $this->belongsTo(GuruProfile::class);
    }
}
