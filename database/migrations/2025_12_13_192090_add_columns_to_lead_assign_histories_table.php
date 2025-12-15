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
        Schema::table('lead_assign_histories', function (Blueprint $table) {
            // Add new columns
            $table->dateTime('last_call_status_date_time')->nullable()->after('call_status_id');
            $table->longText('lead_comment')->nullable()->after('last_call_status_date_time');
            $table->date('meeting_date')->nullable()->after('lead_comment');
            $table->time('meeting_time')->nullable()->after('meeting_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_assign_histories', function (Blueprint $table) {
            // Drop columns
            $table->dropColumn(['last_call_status_date_time', 'lead_comment', 'meeting_date', 'meeting_time']);
        });
    }
};