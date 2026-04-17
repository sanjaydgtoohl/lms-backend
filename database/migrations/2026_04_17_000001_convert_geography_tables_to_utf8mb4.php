<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['regions', 'subregions', 'countries', 'states', 'cities'] as $table) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $table
            ));
        }
    }

    public function down(): void
    {
        foreach (['regions', 'subregions', 'countries', 'states', 'cities'] as $table) {
            DB::statement(sprintf(
                'ALTER TABLE `%s` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci',
                $table
            ));
        }
    }
};
