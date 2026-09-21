<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|numeric|digits_between:1,20|unique:users,username',
            'password' => 'required|string|min:6|confirmed',

            // Data Pribadi
            'nipppk' => 'nullable|string|max:255',
            'nuptk' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'agama' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'nik' => 'nullable|string|max:20',

            // Kepegawaian
            'status_kepegawaian' => 'nullable|string|max:100',
            'pangkat_golongan' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'tmt_golongan' => 'nullable|date',
            'tmt_cpns' => 'nullable|date',
            'tmt_pns_pppk' => 'nullable|date',
            'tmt_sk_sekolah' => 'nullable|date',
            'aktif_ditampilkan' => 'nullable|boolean',

            // Kontak
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'npwp' => 'nullable|string|max:255',
            'no_akta_lahir' => 'nullable|string|max:255',
            'no_bpjs' => 'nullable|string|max:255',
            'profil_singkat' => 'nullable|string',

            // Sosial Media
            'social_instagram' => 'nullable|string|max:255',
            'social_facebook' => 'nullable|string|max:255',
            'social_twitter' => 'nullable|string|max:255',
            'social_tiktok' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            'social_linkedin' => 'nullable|string|max:255',
            'social_website' => 'nullable|string|max:255',
            'social_github' => 'nullable|string|max:255',

            // KGB
            'no_sk_kgb' => 'nullable|string|max:255',
            'tanggal_sk_kgb' => 'nullable|date',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'mkg' => 'nullable|string|max:100',
            'tmt_kgb_akhir' => 'nullable|date',
            'tmt_kgb_berikutnya' => 'nullable|date',

            // Nested relations
            'pendidikan' => 'nullable|array',
            'pendidikan.*.jenjang' => 'nullable|string|max:255',
            'pendidikan.*.jurusan' => 'nullable|string|max:255',
            'pendidikan.*.perguruan_tinggi' => 'nullable|string|max:255',
            'pendidikan.*.tahun_lulus' => 'nullable|string|max:10',
            'pendidikan.*.tempat' => 'nullable|string|max:255',
            'pendidikan.*.nomor_ijazah' => 'nullable|string|max:255',
            'pendidikan.*.tanggal_ijazah' => 'nullable|date',

            'tugas' => 'nullable|array',
            'tugas.*.jenis' => 'nullable|string|max:255',
            'tugas.*.uraian' => 'nullable|string|max:255',
            'tugas.*.jumlah_jam' => 'nullable|integer|min:0',

            'sertifikasi' => 'nullable|array',
            'sertifikasi.*.status' => 'nullable|string|max:100',
            'sertifikasi.*.no_sertifikat' => 'nullable|string|max:255',
            'sertifikasi.*.no_peserta' => 'nullable|string|max:255',
            'sertifikasi.*.no_nrg' => 'nullable|string|max:255',
            'sertifikasi.*.bidang_studi' => 'nullable|string|max:255',
            'sertifikasi.*.penyelenggara' => 'nullable|string|max:255',
            'sertifikasi.*.tahun_lulus' => 'nullable|string|max:10',

            'sk_pengangkatan' => 'nullable|array',
            'sk_pengangkatan.*.kategori' => 'nullable|string|max:255',
            'sk_pengangkatan.*.nomor_sk' => 'nullable|string|max:255',
            'sk_pengangkatan.*.tanggal_sk' => 'nullable|date',
            'sk_pengangkatan.*.pejabat' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nip.numeric' => 'NIP hanya boleh berisi angka.',
            'nip.digits_between' => 'NIP maksimal 20 digit.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
