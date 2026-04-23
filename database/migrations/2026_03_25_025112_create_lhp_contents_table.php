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
        Schema::create('lhp_contents', function (Blueprint $table) {
            $table->comment('Narasi Laporan: Tabel pendukung untuk konten narasi laporan audit');
            $table->uuid('id')->primary();
            $table->foreignUuid('lhp_id')->constrained('lhps')->cascadeOnDelete();
            $table->longText('bab_1_info_umum')->nullable();
            $table->longText('bab_2_hasil_audit')->nullable();
            $table->longText('bab_3_penutup')->nullable();
            $table->json('metadata_tambahan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhp_contents');
    }
};
