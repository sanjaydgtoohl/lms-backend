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
        Schema::table('miss_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('assign_by')->nullable()->comment('User who assigned the campaign');
            $table->unsignedBigInteger('assign_to')->nullable()->comment('User to whom campaign is assigned');
            $table->text('comment')->nullable()->comment('Additional comments about the campaign assignment');
            
            // Foreign key constraints
            $table->foreign('assign_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('set null');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('miss_campaigns', function (Blueprint $table) {
            // Drop columns (this will also drop associated foreign keys)
            $table->dropColumn(['assign_by', 'assign_to', 'comment']);
        });
    }
};
