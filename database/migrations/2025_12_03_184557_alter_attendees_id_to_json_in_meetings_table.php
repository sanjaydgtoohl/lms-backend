<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['attendees_id']);

            // Rename old column
            $table->renameColumn('attendees_id', 'attendees_id_old');
        });

        Schema::table('meetings', function (Blueprint $table) {
            // Create new JSON column
            $table->json('attendees_id')->nullable()->after('lead_id');
        });

        // Optional: migrate existing integer data into JSON format
        DB::table('meetings')->update([
            'attendees_id' => DB::raw("JSON_ARRAY(attendees_id_old)")
        ]);

        Schema::table('meetings', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('attendees_id_old');
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Remove JSON column
            $table->dropColumn('attendees_id');

            // Add old integer column back
            $table->unsignedBigInteger('attendees_id')->nullable();

            // Add FK again
            $table->foreign('attendees_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
