<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_profile_id')->constrained()->cascadeOnDelete();
            $table->string('jenjang')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('perguruan_tinggi')->nullable();
            $table->string('tahun_lulus')->nullable();
            $table->string('tempat')->nullable();
            $table->string('nomor_ijazah')->nullable();
            $table->date('tanggal_ijazah')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_pendidikan');
    }
};
