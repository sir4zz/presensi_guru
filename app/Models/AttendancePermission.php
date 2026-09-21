<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePermission extends Model
{
    protected $fillable = [
        'guru_id',
        'creator_id',
        'tanggal',
        'alasan',
        'status',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
