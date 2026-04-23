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
        Schema::table('findings', function (Blueprint $table) {
            $table->index(['lhp_id', 'created_at'], 'findings_lhp_created_at_idx');
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->index(['finding_id', 'status_tlhp'], 'recommendations_finding_status_tlhp_idx');
        });

        Schema::table('follow_up_evidences', function (Blueprint $table) {
            $table->index(['recommendation_id', 'status_verifikasi'], 'fue_recommendation_status_verifikasi_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_up_evidences', function (Blueprint $table) {
            $table->dropIndex('fue_recommendation_status_verifikasi_idx');
        });

        Schema::table('recommendations', function (Blueprint $table) {
            $table->dropIndex('recommendations_finding_status_tlhp_idx');
        });

        Schema::table('findings', function (Blueprint $table) {
            $table->dropIndex('findings_lhp_created_at_idx');
        });
    }
};
