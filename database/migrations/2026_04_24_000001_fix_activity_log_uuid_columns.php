<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Fix activity_log table to support UUID-based models.
     * 
     * Problem: causer_id & subject_id were created as unsignedBigInteger
     * by nullableMorphs(), but the User model uses HasUuids (UUID strings).
     * 
     * Solution: ALTER columns from bigint to varchar(36) using raw SQL
     * so we don't need doctrine/dbal package.
     */
    public function up()
    {
        // Drop old indexes
        DB::statement('ALTER TABLE `activity_log` DROP INDEX `subject`');
        DB::statement('ALTER TABLE `activity_log` DROP INDEX `causer`');

        // Change column types from bigint to varchar(36) for UUID support
        DB::statement('ALTER TABLE `activity_log` MODIFY `subject_id` VARCHAR(36) NULL');
        DB::statement('ALTER TABLE `activity_log` MODIFY `causer_id` VARCHAR(36) NULL');

        // Re-create indexes
        DB::statement('ALTER TABLE `activity_log` ADD INDEX `subject` (`subject_type`, `subject_id`)');
        DB::statement('ALTER TABLE `activity_log` ADD INDEX `causer` (`causer_type`, `causer_id`)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE `activity_log` DROP INDEX `subject`');
        DB::statement('ALTER TABLE `activity_log` DROP INDEX `causer`');

        DB::statement('ALTER TABLE `activity_log` MODIFY `subject_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `activity_log` MODIFY `causer_id` BIGINT UNSIGNED NULL');

        DB::statement('ALTER TABLE `activity_log` ADD INDEX `subject` (`subject_type`, `subject_id`)');
        DB::statement('ALTER TABLE `activity_log` ADD INDEX `causer` (`causer_type`, `causer_id`)');
    }
};
