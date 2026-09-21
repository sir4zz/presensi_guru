<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('accuracy_masuk', 8, 2)->nullable()->after('distance_masuk');
            $table->decimal('accuracy_pulang', 8, 2)->nullable()->after('distance_pulang');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['accuracy_masuk', 'accuracy_pulang']);
        });
    }
};
