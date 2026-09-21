<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_profile_id')->constrained()->cascadeOnDelete();
            $table->string('jenis')->nullable();
            $table->string('uraian')->nullable();
            $table->integer('jumlah_jam')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_tugas');
    }
};
