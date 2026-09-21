<?php

namespace Database\Seeders;

use App\Models\GuruProfile;
use App\Models\GuruPendidikan;
use App\Models\GuruTugas;
use App\Models\GuruSertifikasi;
use App\Models\GuruSkPengangkatan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $guru1 = User::create([
            'name' => 'Budi Santoso',
            'username' => '1987654321',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'status' => 'aktif',
        ]);

        $profile1 = GuruProfile::create([
            'user_id' => $guru1->id,
            'nip' => '1987654321',
            'nipppk' => '2019001234',
            'nuptk' => '5678901234567890',
            'jenis_kelamin' => 'laki-laki',
            'agama' => 'Islam',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '1978-05-15',
            'nik' => '3578011505780001',
            'status_kepegawaian' => 'PNS',
            'pangkat_golongan' => 'Pembina Tk. I / IV/b',
            'jabatan' => 'Guru Mata Pelajaran',
            'tmt_golongan' => '2019-04-01',
            'tmt_cpns' => '2005-09-01',
            'tmt_pns_pppk' => '2006-09-01',
            'tmt_sk_sekolah' => '2019-07-15',
            'aktif_ditampilkan' => true,
            'alamat' => 'Jl. Raya Wonokromo No. 123, Surabaya',
            'no_hp' => '081234567890',
            'email' => 'budi.santoso@guru.sch.id',
            'npwp' => '123456789012000',
            'no_bpjs' => '0001234567890',
            'profil_singkat' => 'Guru Pendidikan Teknologi Informasi dengan pengalaman mengajar lebih dari 15 tahun.',
            'social_instagram' => 'budi_santoso',
            'social_linkedin' => 'budi-santoso',
            'no_sk_kgb' => 'SK/KGB/2024/001',
            'tanggal_sk_kgb' => '2024-01-01',
            'gaji_pokok' => 4500000,
            'mkg' => 'III.D',
            'tmt_kgb_akhir' => '2024-12-31',
            'tmt_kgb_berikutnya' => '2025-01-01',
        ]);

        GuruPendidikan::create([
            'guru_profile_id' => $profile1->id,
            'jenjang' => 'S1',
            'jurusan' => 'Teknologi Informasi',
            'perguruan_tinggi' => 'Universitas Negeri Surabaya',
            'tahun_lulus' => '2004',
            'tempat' => 'Surabaya',
            'nomor_ijazah' => 'IJZ-2004-00123',
            'tanggal_ijazah' => '2004-08-15',
        ]);

        GuruTugas::create([
            'guru_profile_id' => $profile1->id,
            'jenis' => 'Tugas Tambahan',
            'uraian' => 'Wali Kelas XII RPL 1',
            'jumlah_jam' => 6,
        ]);

        GuruSertifikasi::create([
            'guru_profile_id' => $profile1->id,
            'status' => 'Sudah Sertifikasi',
            'no_sertifikat' => 'PS-2015-123456',
            'no_peserta' => 'PTK-2015-789',
            'no_nrg' => '987654321012345',
            'bidang_studi' => 'Teknologi Informasi',
            'penyelenggara' => 'LPTK Universitas Negeri',
            'tahun_lulus' => '2015',
        ]);

        GuruSkPengangkatan::create([
            'guru_profile_id' => $profile1->id,
            'kategori' => 'SK Awal (Sekolah)',
            'nomor_sk' => 'SK/2019/001',
            'tanggal_sk' => '2019-07-15',
            'pejabat' => 'Kepala Dinas Pendidikan Kota Surabaya',
        ]);

        $guru2 = User::create([
            'name' => 'Siti Aminah',
            'username' => '1988123456',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'status' => 'aktif',
        ]);

        $profile2 = GuruProfile::create([
            'user_id' => $guru2->id,
            'nip' => '1988123456',
            'nipppk' => '2019005678',
            'nuptk' => '1234567890123456',
            'jenis_kelamin' => 'perempuan',
            'agama' => 'Islam',
            'tempat_lahir' => 'Malang',
            'tanggal_lahir' => '1988-12-10',
            'nik' => '3573015012880002',
            'status_kepegawaian' => 'PNS',
            'pangkat_golongan' => 'Penata Tk. I / III/d',
            'jabatan' => 'Guru Mata Pelajaran',
            'tmt_golongan' => '2020-04-01',
            'tmt_cpns' => '2010-01-01',
            'tmt_pns_pppk' => '2011-01-01',
            'tmt_sk_sekolah' => '2019-07-15',
            'aktif_ditampilkan' => true,
            'alamat' => 'Jl. Dinoyo No. 45, Malang',
            'no_hp' => '085678901234',
            'email' => 'siti.aminah@guru.sch.id',
            'profil_singkat' => 'Guru Bahasa Indonesia yang berdedikasi.',
            'social_instagram' => 'siti_aminah',
        ]);

        GuruPendidikan::create([
            'guru_profile_id' => $profile2->id,
            'jenjang' => 'S1',
            'jurusan' => 'Pendidikan Bahasa Indonesia',
            'perguruan_tinggi' => 'Universitas Negeri Malang',
            'tahun_lulus' => '2010',
            'tempat' => 'Malang',
            'nomor_ijazah' => 'IJZ-2010-00456',
            'tanggal_ijazah' => '2010-07-20',
        ]);

        GuruSertifikasi::create([
            'guru_profile_id' => $profile2->id,
            'status' => 'Sudah Sertifikasi',
            'no_sertifikat' => 'PS-2018-654321',
            'no_peserta' => 'PTK-2018-321',
            'no_nrg' => '123456789012345',
            'bidang_studi' => 'Bahasa Indonesia',
            'penyelenggara' => 'LPTK Universitas Negeri',
            'tahun_lulus' => '2018',
        ]);

        GuruSkPengangkatan::create([
            'guru_profile_id' => $profile2->id,
            'kategori' => 'SK Awal (Sekolah)',
            'nomor_sk' => 'SK/2019/002',
            'tanggal_sk' => '2019-07-15',
            'pejabat' => 'Kepala Dinas Pendidikan Kota Malang',
        ]);
    }
}
