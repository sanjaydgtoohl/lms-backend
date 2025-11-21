<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lead_assign_histories', function (Blueprint $table) {
            // Drop foreign keys if they exist (check database directly)
            $this->dropForeignKeyIfExists('lead_assign_histories', 'lead_assign_histories_lead_id_foreign', $table);
            $this->dropForeignKeyIfExists('lead_assign_histories', 'lead_assign_histories_assign_user_id_foreign', $table);
            $this->dropForeignKeyIfExists('lead_assign_histories', 'lead_assign_histories_current_user_id_foreign', $table);
        });
    }

    private function dropForeignKeyIfExists($tableName, $keyName, $table)
    {
        try {
            $constraints = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = ?", [$tableName, $keyName]);
            if (!empty($constraints)) {
                $table->dropForeign($keyName);
            }
        } catch (\Exception $e) {
            // Silently continue if foreign key doesn't exist
        }
    }

    public function down(): void
    {
        Schema::table('lead_assign_histories', function (Blueprint $table) {
            // Re-add the FKs with correct definitions
            $table->foreign('lead_id')
                  ->references('id')->on('leads')
                  ->onDelete('cascade');

            $table->foreign('assign_user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('current_user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }
};
