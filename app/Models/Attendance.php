<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
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
        'lat_pulang',
        'lng_pulang',
        'distance_pulang',
        'keterangan',
        'bukti_file',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
