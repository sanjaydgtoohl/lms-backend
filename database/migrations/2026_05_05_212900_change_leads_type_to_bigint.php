<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * UpdateLeadsTableChangeTypeToLeadTypeId Migration
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // rename column
            $table->renameColumn('type', 'lead_type_id');
        });

        Schema::table('leads', function (Blueprint $table) {
            // change datatype
            $table->unsignedBigInteger('lead_type_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->renameColumn('lead_type_id', 'type');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->enum('type', ['Agency', 'Brand'])->nullable()->change();
        });
    }
};