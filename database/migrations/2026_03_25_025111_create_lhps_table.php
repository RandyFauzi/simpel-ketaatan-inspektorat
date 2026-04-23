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
        Schema::create('lhps', function (Blueprint $table) {
            $table->comment('Header Laporan: Tabel utama penyimpan identitas laporan audit');
            $table->uuid('id')->primary();
            $table->string('nomor_lhp')->unique();
            $table->date('tgl_lhp');
            $table->string('judul');
            $table->year('tahun_anggaran');
            $table->foreignUuid('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhps');
    }
};
