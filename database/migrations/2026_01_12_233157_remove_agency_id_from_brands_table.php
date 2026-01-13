<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // Drop FK first
            $table->dropForeign('brands_agency_id_foreign');

            // Then drop column
            $table->dropColumn('agency_id');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('agency_id');

            // Re-add foreign key
            $table->foreign('agency_id')
                  ->references('id')
                  ->on('agency');
        });
    }
};

