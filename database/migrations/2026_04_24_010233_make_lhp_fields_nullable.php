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
        // Mengubah kolom menjadi nullable menggunakan raw SQL agar tidak perlu doctrine/dbal
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `lhps` MODIFY `nomor_lhp` VARCHAR(100) NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `lhps` MODIFY `tgl_lhp` DATE NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `lhps` MODIFY `judul` VARCHAR(500) NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `lhps` MODIFY `tahun_anggaran` YEAR NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `lhps` MODIFY `opd_id` CHAR(36) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 
    }
};
