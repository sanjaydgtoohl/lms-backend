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
        Schema::table('brands', function (Blueprint $table) {
            // --- Removal of Columns ---
            $table->dropColumn('region_id');
            $table->dropColumn('subregion_id');

            // --- Addition of New Columns ---
            
            // Add agency_id (Assuming it references an 'agencies' table, nullable, and uses a standard big integer for foreign keys)
            $table->foreignId('agency_id')
                  ->nullable()
                  ->after('city_id') // Placing it logically after location data
                  ->constrained('agencies')
                  ->nullOnDelete();

            // Add zone_id (Assuming it references a 'zones' table, nullable)
            $table->foreignId('zone_id')
                  ->nullable()
                  ->after('agency_id') // Placing it after agency_id
                  ->constrained('zones')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // --- Reversing the Additions (Dropping new columns) ---
            $table->dropConstrainedForeignId('agency_id');
            $table->dropConstrainedForeignId('zone_id');

            // --- Reversing the Removals (Adding original columns back) ---
            // These are added back as unsigned integers to match the original schema snippet
            $table->unsignedInteger('region_id')->after('city_id');
            $table->unsignedInteger('subregion_id')->after('region_id');
        });
    }
};