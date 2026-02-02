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
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('meeting_date');
            $table->dropColumn('meeting_time');
            
            $table->dateTime('meeting_start_date')->nullable()->after('link')->comment('Start date and time of the meeting');
            $table->dateTime('meeting_end_date')->nullable()->after('meeting_start_date')->comment('End date and time of the meeting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            
            $table->dropColumn('meeting_start_date');
            $table->dropColumn('meeting_end_date');
            
            $table->date('meeting_date')->nullable()->after('link')->comment('Future date of the meeting');
            $table->time('meeting_time')->nullable()->after('meeting_date')->comment('Future time of the meeting');
        });
    }
};
