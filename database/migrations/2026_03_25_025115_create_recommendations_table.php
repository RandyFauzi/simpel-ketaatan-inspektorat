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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->comment('Rekomendasi: Solusi yang harus dilakukan SKPD berdasarkan temuan');
            $table->uuid('id')->primary();
            $table->foreignUuid('finding_id')->constrained('findings')->cascadeOnDelete();
            $table->string('kode_rekomendasi');
            $table->text('uraian_rekomendasi');
            $table->enum('status', ['belum_sesuai', 'proses', 'sesuai'])->default('belum_sesuai');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
