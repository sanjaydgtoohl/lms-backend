<?php

/**
 * AlterTableToRemoveNullInBrands Migration
 * -----------------------------------------
 * This migration modifies the brands table to allow null values for
 * specific foreign key columns, providing flexibility in data entry.
 *
 * @package Database\Migrations
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

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
            $table->unsignedBigInteger('brand_type_id')->nullable()->change();
            $table->unsignedBigInteger('industry_id')->nullable()->change();
            $table->unsignedBigInteger('country_id')->nullable()->change();
            $table->unsignedBigInteger('state_id')->nullable()->change();
            $table->unsignedBigInteger('city_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_type_id')->nullable(false)->change();
            $table->unsignedBigInteger('industry_id')->nullable(false)->change();
            $table->unsignedBigInteger('country_id')->nullable(false)->change();
            $table->unsignedBigInteger('state_id')->nullable(false)->change();
            $table->unsignedBigInteger('city_id')->nullable(false)->change();
        });
    }
};
