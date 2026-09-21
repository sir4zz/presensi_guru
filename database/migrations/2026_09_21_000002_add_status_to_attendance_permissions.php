<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('attendance_permissions', 'status')) {
            Schema::table('attendance_permissions', function (Blueprint $table) {
                $table->string('status')->default('izin')->after('alasan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('attendance_permissions', 'status')) {
            Schema::table('attendance_permissions', fn (Blueprint $table) => $table->dropColumn('status'));
        }
    }
};
