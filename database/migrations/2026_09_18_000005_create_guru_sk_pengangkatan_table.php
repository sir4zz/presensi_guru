<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_sk_pengangkatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_profile_id')->constrained()->cascadeOnDelete();
            $table->string('kategori')->nullable();
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->string('pejabat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_sk_pengangkatan');
    }
};
