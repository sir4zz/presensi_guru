<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nip',
        'sk',
        'spmt',
        'foto',
        'nipppk',
        'nuptk',
        'jenis_kelamin',
        'agama',
        'tempat_lahir',
        'tanggal_lahir',
        'nik',
        'status_kepegawaian',
        'pangkat_golongan',
        'jabatan',
        'tmt_golongan',
        'tmt_cpns',
        'tmt_pns_pppk',
        'tmt_sk_sekolah',
        'aktif_ditampilkan',
        'alamat',
        'no_hp',
        'email',
        'npwp',
        'no_akta_lahir',
        'no_bpjs',
        'profil_singkat',
        'social_instagram',
        'social_facebook',
        'social_twitter',
        'social_tiktok',
        'social_youtube',
        'social_linkedin',
        'social_website',
        'social_github',
        'no_sk_kgb',
        'tanggal_sk_kgb',
        'gaji_pokok',
        'mkg',
        'tmt_kgb_akhir',
        'tmt_kgb_berikutnya',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tmt_golongan' => 'date',
        'tmt_cpns' => 'date',
        'tmt_pns_pppk' => 'date',
        'tmt_sk_sekolah' => 'date',
        'aktif_ditampilkan' => 'boolean',
        'tanggal_sk_kgb' => 'date',
        'gaji_pokok' => 'decimal:2',
        'tmt_kgb_akhir' => 'date',
        'tmt_kgb_berikutnya' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pendidikan()
    {
        return $this->hasMany(GuruPendidikan::class);
    }

    public function tugas()
    {
        return $this->hasMany(GuruTugas::class);
    }

    public function sertifikasi()
    {
        return $this->hasMany(GuruSertifikasi::class);
    }

    public function skPengangkatan()
    {
        return $this->hasMany(GuruSkPengangkatan::class);
    }
}
