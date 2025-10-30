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
        Schema::table('agency', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['brand_id']);

            // Then modify column to nullable
            $table->unsignedBigInteger('brand_id')->nullable()->change();

            // Re-add foreign key with desired behavior
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->nullOnDelete();    // or ->onDelete('set null')
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agency', function (Blueprint $table) {
            // Drop updated FK
            $table->dropForeign(['brand_id']);

            // Change back to NOT NULL (original state)
            $table->unsignedBigInteger('brand_id')->nullable(false)->change();

            // Re-add original FK (cascade delete)
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->onDelete('cascade');
        });
    }
};
