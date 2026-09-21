<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru_profiles', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('user_id');
            $table->string('nipppk')->nullable()->after('nip');
            $table->string('nuptk')->nullable()->after('nipppk');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable()->after('nuptk');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->string('tempat_lahir')->nullable()->after('agama');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('nik')->nullable()->after('tanggal_lahir');

            // Kepegawaian
            $table->string('status_kepegawaian')->nullable()->after('nik');
            $table->string('pangkat_golongan')->nullable()->after('status_kepegawaian');
            $table->string('jabatan')->nullable()->after('pangkat_golongan');
            $table->date('tmt_golongan')->nullable()->after('jabatan');
            $table->date('tmt_cpns')->nullable()->after('tmt_golongan');
            $table->date('tmt_pns_pppk')->nullable()->after('tmt_cpns');
            $table->date('tmt_sk_sekolah')->nullable()->after('tmt_pns_pppk');
            $table->boolean('aktif_ditampilkan')->default(true)->after('tmt_sk_sekolah');

            // Kontak
            $table->text('alamat')->nullable()->after('aktif_ditampilkan');
            $table->string('no_hp')->nullable()->after('alamat');
            $table->string('email')->nullable()->after('no_hp');
            $table->string('npwp')->nullable()->after('email');
            $table->string('no_akta_lahir')->nullable()->after('npwp');
            $table->string('no_bpjs')->nullable()->after('no_akta_lahir');
            $table->text('profil_singkat')->nullable()->after('no_bpjs');

            // Sosial Media
            $table->string('social_instagram')->nullable()->after('profil_singkat');
            $table->string('social_facebook')->nullable()->after('social_instagram');
            $table->string('social_twitter')->nullable()->after('social_facebook');
            $table->string('social_tiktok')->nullable()->after('social_twitter');
            $table->string('social_youtube')->nullable()->after('social_tiktok');
            $table->string('social_linkedin')->nullable()->after('social_youtube');
            $table->string('social_website')->nullable()->after('social_linkedin');
            $table->string('social_github')->nullable()->after('social_website');

            // KGB
            $table->string('no_sk_kgb')->nullable()->after('social_github');
            $table->date('tanggal_sk_kgb')->nullable()->after('no_sk_kgb');
            $table->decimal('gaji_pokok', 15, 2)->nullable()->after('tanggal_sk_kgb');
            $table->string('mkg')->nullable()->after('gaji_pokok');
            $table->date('tmt_kgb_akhir')->nullable()->after('mkg');
            $table->date('tmt_kgb_berikutnya')->nullable()->after('tmt_kgb_akhir');
        });
    }

    public function down(): void
    {
        Schema::table('guru_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'foto', 'nipppk', 'nuptk', 'jenis_kelamin', 'agama', 'tempat_lahir',
                'tanggal_lahir', 'nik', 'status_kepegawaian', 'pangkat_golongan', 'jabatan',
                'tmt_golongan', 'tmt_cpns', 'tmt_pns_pppk', 'tmt_sk_sekolah', 'aktif_ditampilkan',
                'alamat', 'no_hp', 'email', 'npwp', 'no_akta_lahir', 'no_bpjs', 'profil_singkat',
                'social_instagram', 'social_facebook', 'social_twitter', 'social_tiktok',
                'social_youtube', 'social_linkedin', 'social_website', 'social_github',
                'no_sk_kgb', 'tanggal_sk_kgb', 'gaji_pokok', 'mkg', 'tmt_kgb_akhir', 'tmt_kgb_berikutnya',
            ]);
        });
    }
};
