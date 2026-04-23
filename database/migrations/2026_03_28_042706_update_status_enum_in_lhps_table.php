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
        // For MySQL, you often need to run a raw query to alter an ENUM safely without losing data
        DB::statement("ALTER TABLE lhps MODIFY status ENUM('draft', 'in_review', 'revision_needed', 'published', 'closed') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE lhps MODIFY status ENUM('draft', 'published', 'closed') DEFAULT 'draft'");
    }
};
