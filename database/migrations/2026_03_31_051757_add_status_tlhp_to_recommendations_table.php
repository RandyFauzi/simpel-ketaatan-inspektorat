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
        Schema::table('recommendations', function (Blueprint $table) {
            $table->enum('status_tlhp', ['belum_selesai', 'dalam_proses', 'selesai', 'tidak_dapat_ditindaklanjuti'])
                  ->default('belum_selesai')
                  ->after('nilai_rekomendasi');
            $table->text('catatan_tlhp')->nullable()->after('status_tlhp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropColumn(['status_tlhp', 'catatan_tlhp']);
        });
    }
};
