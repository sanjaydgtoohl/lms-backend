<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropLeadContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop foreign key first (avoids SQL error 3730)
        if (Schema::hasTable('lead_contacts')) {

            Schema::table('lead_contacts', function (Blueprint $table) {
                // Only drop if column exists
                if (Schema::hasColumn('lead_contacts', 'lead_id')) {
                    $table->dropForeign(['lead_id']);
                }
            });

            // Now drop table safely
            Schema::dropIfExists('lead_contacts');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optional: recreate table if needed
        // (not required in your case)
    }
}
