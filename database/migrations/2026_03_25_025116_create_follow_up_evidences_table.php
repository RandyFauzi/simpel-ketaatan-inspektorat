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
        Schema::create('follow_up_evidences', function (Blueprint $table) {
            $table->comment('Bukti & Progres: Tempat SKPD mengunggah bukti, nominal, serta log verifikasi dari Auditor');
            $table->uuid('id')->primary();
            $table->foreignUuid('recommendation_id')->constrained('recommendations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->decimal('nominal_setoran', 20, 2)->default(0);
            $table->enum('status_verifikasi', ['submitted', 'approved', 'rejected'])->default('submitted');
            $table->text('catatan_verifikator')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_evidences');
    }
};
