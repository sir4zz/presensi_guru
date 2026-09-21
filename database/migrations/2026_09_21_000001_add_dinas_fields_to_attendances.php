<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kompatibel dengan instalasi lama yang sudah menjalankan migration awal.
        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE attendances MODIFY status ENUM('hadir','terlambat','izin','sakit','alpha','tugas_luar','dinas_luar') NOT NULL DEFAULT 'hadir'");
        }
        if (!Schema::hasColumn('attendances', 'keperluan_dinas')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->text('keperluan_dinas')->nullable();
                $table->string('lokasi_dinas')->nullable();
                $table->string('surat_tugas_file')->nullable();
                $table->foreignId('dinas_verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('dinas_verified_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendances', 'keperluan_dinas')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropForeign(['dinas_verified_by']);
                $table->dropColumn(['keperluan_dinas', 'lokasi_dinas', 'surat_tugas_file', 'dinas_verified_by', 'dinas_verified_at']);
            });
        }
    }
};
