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
        Schema::table('lhps', function (Blueprint $table) {
            $table->index('status', 'lhps_status_idx');
            $table->index('tim', 'lhps_tim_idx');
            $table->index('tgl_lhp', 'lhps_tgl_lhp_idx');
            $table->index(['tim', 'status'], 'lhps_tim_status_idx');
            $table->index(['opd_id', 'status'], 'lhps_opd_status_idx');
        });

        Schema::table('findings', function (Blueprint $table) {
            $table->index('created_at', 'findings_created_at_idx');
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->index('status', 'recommendations_status_idx');
            $table->index('status_tlhp', 'recommendations_status_tlhp_idx');
        });

        Schema::table('follow_up_evidences', function (Blueprint $table) {
            $table->index('status_verifikasi', 'fue_status_verifikasi_idx');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_idx');
            $table->index('tim', 'users_tim_idx');
            $table->index(['role', 'tim'], 'users_role_tim_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_tim_idx');
            $table->dropIndex('users_tim_idx');
            $table->dropIndex('users_role_idx');
        });

        Schema::table('follow_up_evidences', function (Blueprint $table) {
            $table->dropIndex('fue_status_verifikasi_idx');
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropIndex('recommendations_status_tlhp_idx');
            $table->dropIndex('recommendations_status_idx');
        });

        Schema::table('findings', function (Blueprint $table) {
            $table->dropIndex('findings_created_at_idx');
        });

        Schema::table('lhps', function (Blueprint $table) {
            $table->dropIndex('lhps_opd_status_idx');
            $table->dropIndex('lhps_tim_status_idx');
            $table->dropIndex('lhps_tgl_lhp_idx');
            $table->dropIndex('lhps_tim_idx');
            $table->dropIndex('lhps_status_idx');
        });
    }
};
