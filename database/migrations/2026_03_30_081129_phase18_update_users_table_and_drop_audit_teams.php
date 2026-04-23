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
        // 1. Drop audit_teams pivot table completely
        Schema::dropIfExists('audit_teams');

        // 2. Modify users table
        // For SQLite, modifying ENUMs can be tricky. We just drop the old 'role', 'opd_id' 
        // and recreate 'role' and 'tim'.
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key for opd_id (SQLite friendly way if it exists)
            // But since Laravel 11 handles it:
            $table->dropForeign(['opd_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'opd_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'admin', 
                'inspektur_daerah', 
                'inspektur_pembantu_1', 
                'ketua_tim', 
                'auditor'
            ])->default('auditor')->after('email');
            
            $table->enum('tim', [
                'tim_1', 
                'tim_2'
            ])->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting back to previous state if needed
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'tim']);
            $table->enum('role', ['admin', 'auditor', 'skpd'])->default('skpd')->after('email');
            $table->foreignUuid('opd_id')->nullable()->after('role')->constrained('opds')->nullOnDelete();
        });

        Schema::create('audit_teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lhp_id')->constrained('lhps')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['penanggung_jawab', 'pengendali_teknis', 'ketua_tim', 'anggota']);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
