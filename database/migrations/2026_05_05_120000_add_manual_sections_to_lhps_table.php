<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            if (!Schema::hasColumn('lhps', 'simpulan_manual')) {
                $table->longText('simpulan_manual')->nullable()->after('opd_id');
            }

            if (!Schema::hasColumn('lhps', 'rekomendasi_manual')) {
                $table->longText('rekomendasi_manual')->nullable()->after('simpulan_manual');
            }

            if (!Schema::hasColumn('lhps', 'penutup_manual')) {
                $table->longText('penutup_manual')->nullable()->after('rekomendasi_manual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lhps', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('lhps', 'penutup_manual')) {
                $columns[] = 'penutup_manual';
            }

            if (Schema::hasColumn('lhps', 'rekomendasi_manual')) {
                $columns[] = 'rekomendasi_manual';
            }

            if (Schema::hasColumn('lhps', 'simpulan_manual')) {
                $columns[] = 'simpulan_manual';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
