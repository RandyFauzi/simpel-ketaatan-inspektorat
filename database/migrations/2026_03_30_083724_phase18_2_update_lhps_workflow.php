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
            $table->enum('tim', ['tim_1', 'tim_2'])->nullable()->after('opd_id');
        });

        // Update enum for MySQL
        DB::statement("ALTER TABLE lhps MODIFY status ENUM('draft', 'review_ketua', 'review_irban', 'review_inspektur', 'published') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            $table->dropColumn('tim');
        });

        DB::statement("ALTER TABLE lhps MODIFY status ENUM('draft', 'in_review', 'revision_needed', 'published', 'closed') DEFAULT 'draft'");
    }
};
