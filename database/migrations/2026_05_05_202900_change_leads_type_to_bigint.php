<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `leads` CHANGE COLUMN `type` `lead_type_id` BIGINT UNSIGNED NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `leads` CHANGE COLUMN `lead_type_id` `type` ENUM('Agency','Brand') NULL");
    }
};
