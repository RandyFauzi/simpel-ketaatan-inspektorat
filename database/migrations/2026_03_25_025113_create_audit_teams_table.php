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
        Schema::create('audit_teams', function (Blueprint $table) {
            $table->comment('Pivot Tim: Menentukan peran pengguna pada suatu LHP (Inspektur, Ketua Tim, Anggota)');
            $table->uuid('id')->primary();
            $table->foreignUuid('lhp_id')->constrained('lhps')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['penanggung_jawab', 'pengendali_teknis', 'ketua_tim', 'anggota']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_teams');
    }
};
