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
        Schema::create('findings', function (Blueprint $table) {
            $table->comment('Temuan: Menampung detail penyimpangan beserta kerugian finansial');
            $table->uuid('id')->primary();
            $table->foreignUuid('lhp_id')->constrained('lhps')->cascadeOnDelete();
            $table->string('kode_temuan');
            $table->text('uraian_temuan');
            $table->decimal('kerugian_negara', 20, 2)->default(0);
            $table->decimal('kerugian_daerah', 20, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('findings');
    }
};
