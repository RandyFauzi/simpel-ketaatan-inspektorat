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
        // Alter User Role ENUM to include inspektur_pembantu without destroying data
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'auditor', 'skpd', 'inspektur_pembantu') NOT NULL DEFAULT 'skpd'");

        // Drop hanging table from previous failed migration
        \Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS lhp_reviews');

        Schema::create('lhp_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lhp_id')->constrained('lhps')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('catatan');
            $table->enum('status_perbaikan', ['pending', 'diperbaiki'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lhp_reviews');
    }
};
