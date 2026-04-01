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
        Schema::table('briefs', function (Blueprint $table) {
            $table->date('campaign_start_date')->nullable()->after('budget');
            $table->date('campaign_end_date')->nullable()->after('campaign_start_date');
            $table->integer('campaign_duration')->nullable()->after('campaign_end_date')->comment('Duration in days');
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('briefs', function (Blueprint $table) {
            $table->dropColumn('campaign_start_date');
            $table->dropColumn('campaign_end_date');    
            $table->dropColumn('campaign_duration');
            $table->enum('status', ['1', '2', '15'])
                ->default('2')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted')
                ->change();
        });
    }
};
