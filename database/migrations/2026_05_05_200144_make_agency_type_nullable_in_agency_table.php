<?php

/**
 * UpdateAgencyTypeNullable Migration
 * -----------------------------------------
 * This migration modifies the agency table to make the agency_type
 * column nullable, allowing flexibility in storing agency type data.
 *
 * @package Database\Migrations
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            $table->unsignedBigInteger('agency_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        $defaultAgencyTypeId = DB::table('agency_type')->min('id');

        if ($defaultAgencyTypeId !== null) {
            DB::table('agency')
                ->whereNull('agency_type')
                ->update(['agency_type' => $defaultAgencyTypeId]);
        } else {
            DB::table('agency')
                ->whereNull('agency_type')
                ->delete();
        }

        Schema::table('agency', function (Blueprint $table) {
            $table->unsignedBigInteger('agency_type')->nullable(false)->change();
        });
    }
};
