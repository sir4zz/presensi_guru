<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruTugas extends Model
{
    protected $table = 'guru_tugas';

    protected $fillable = [
        'guru_profile_id',
        'jenis',
        'uraian',
        'jumlah_jam',
    ];

    protected $casts = [
        'jumlah_jam' => 'integer',
    ];

    public function guruProfile()
    {
        return $this->belongsTo(GuruProfile::class);
    }
}
