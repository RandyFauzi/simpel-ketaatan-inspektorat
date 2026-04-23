<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('findings', function (Blueprint $table) {
            $table->text('kondisi')->nullable()->after('uraian_temuan')->comment('Kondisi: Apa yang ditemukan');
            $table->text('kriteria')->nullable()->after('kondisi')->comment('Kriteria: Peraturan yang dilanggar');
            $table->text('sebab')->nullable()->after('kriteria')->comment('Sebab: Penyebab penyimpangan');
            $table->text('akibat')->nullable()->after('sebab')->comment('Akibat: Dampak dari penyimpangan');
            $table->text('rekomendasi_teks')->nullable()->after('akibat')->comment('Rekomendasi narasi untuk PDF');
        });
    }

    public function down(): void
    {
        Schema::table('findings', function (Blueprint $table) {
            $table->dropColumn(['kondisi', 'kriteria', 'sebab', 'akibat', 'rekomendasi_teks']);
        });
    }
};
