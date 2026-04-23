<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            $table->foreignUuid('created_by')->nullable()->after('tim')->constrained('users')->nullOnDelete();
        });

        // Backfill data historis dari jejak pembuatan draft jika tersedia.
        DB::statement("
            UPDATE lhps l
            JOIN (
                SELECT ll.lhp_id, ll.user_id
                FROM lhp_logs ll
                INNER JOIN (
                    SELECT lhp_id, MIN(id) AS first_log_id
                    FROM lhp_logs
                    GROUP BY lhp_id
                ) first_logs ON first_logs.first_log_id = ll.id
            ) x ON x.lhp_id = l.id
            SET l.created_by = x.user_id
            WHERE l.created_by IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
