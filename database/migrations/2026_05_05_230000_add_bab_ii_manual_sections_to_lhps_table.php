<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            if (!Schema::hasColumn('lhps', 'penilaian_ketaatan')) {
                $table->longText('penilaian_ketaatan')->nullable()->after('penutup_manual');
            }

            if (!Schema::hasColumn('lhps', 'kesesuaian_output')) {
                $table->longText('kesesuaian_output')->nullable()->after('penilaian_ketaatan');
            }

            if (!Schema::hasColumn('lhps', 'hal_penting')) {
                $table->longText('hal_penting')->nullable()->after('kesesuaian_output');
            }

            if (!Schema::hasColumn('lhps', 'tindak_lanjut')) {
                $table->longText('tindak_lanjut')->nullable()->after('hal_penting');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            $columns = [];

            foreach (['tindak_lanjut', 'hal_penting', 'kesesuaian_output', 'penilaian_ketaatan'] as $column) {
                if (Schema::hasColumn('lhps', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
