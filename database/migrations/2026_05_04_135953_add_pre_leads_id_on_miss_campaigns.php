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
            $table->unsignedBigInteger('leads_id')->nullable()->after('id');
            $table->foreign('leads_id')->references('id')->on('leads')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('miss_campaigns', function (Blueprint $table) {
            $table->dropForeign('miss_campaigns_leads_id_foreign');
            $table->dropColumn('leads_id');
        });
    }
};