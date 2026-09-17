<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'tugas_luar'])->default('hadir');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('lng_masuk', 10, 7)->nullable();
            $table->decimal('distance_masuk', 8, 2)->nullable();
            $table->decimal('lat_pulang', 10, 7)->nullable();
            $table->decimal('lng_pulang', 10, 7)->nullable();
            $table->decimal('distance_pulang', 8, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('bukti_file')->nullable();
            $table->timestamps();

            $table->unique(['guru_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
