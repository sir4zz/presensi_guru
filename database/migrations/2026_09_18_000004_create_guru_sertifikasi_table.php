<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_sertifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_profile_id')->constrained()->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->string('no_sertifikat')->nullable();
            $table->string('no_peserta')->nullable();
            $table->string('no_nrg')->nullable();
            $table->string('bidang_studi')->nullable();
            $table->string('penyelenggara')->nullable();
            $table->string('tahun_lulus')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_sertifikasi');
    }
};
