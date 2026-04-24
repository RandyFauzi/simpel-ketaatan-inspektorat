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
        // Ubah tipe data notifiable_id dari unsignedBigInteger ke CHAR(36)
        // karena model User menggunakan UUID.
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `notifications` MODIFY `notifiable_id` CHAR(36) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uuid', function (Blueprint $table) {
            //
        });
    }
};
