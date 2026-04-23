<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Users: restore SKPD compatibility + OPD mapping + soft delete support
        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('admin', 'inspektur_daerah', 'inspektur_pembantu_1', 'ketua_tim', 'auditor', 'skpd')
            NOT NULL DEFAULT 'auditor'
        ");

        if (!Schema::hasColumn('users', 'opd_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignUuid('opd_id')->nullable()->after('tim')->constrained('opds')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 2) Follow-up evidence: unify status_verifikasi to pending/approved/rejected
        // Keep old value "submitted" temporarily to allow data migration.
        DB::statement("
            ALTER TABLE follow_up_evidences
            MODIFY status_verifikasi ENUM('submitted', 'pending', 'approved', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
        DB::table('follow_up_evidences')
            ->where('status_verifikasi', 'submitted')
            ->update(['status_verifikasi' => 'pending']);
        DB::statement("
            ALTER TABLE follow_up_evidences
            MODIFY status_verifikasi ENUM('pending', 'approved', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback follow_up_evidences enum
        DB::statement("
            ALTER TABLE follow_up_evidences
            MODIFY status_verifikasi ENUM('submitted', 'approved', 'rejected')
            NOT NULL DEFAULT 'submitted'
        ");

        // Rollback users table changes
        if (Schema::hasColumn('users', 'opd_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['opd_id']);
                $table->dropColumn('opd_id');
            });
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        DB::statement("
            ALTER TABLE users
            MODIFY role ENUM('admin', 'inspektur_daerah', 'inspektur_pembantu_1', 'ketua_tim', 'auditor')
            NOT NULL DEFAULT 'auditor'
        ");
    }
};

