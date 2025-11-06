<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
    {
        /**
         * Run the migrations.
         */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Convert existing text statuses to numeric values first
            DB::table('users')->where('status', 'active')->update(['status' => '1']);
            DB::table('users')->where('status', 'inactive')->update(['status' => '2']);
            DB::table('users')->where('status', 'suspended')->update(['status' => '3']);
        });

        Schema::table('users', function (Blueprint $table) {
            // Now modify the column safely
            $table->enum('status', ['1', '2', '3', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 3 = suspended, 15 = user soft delete')
                ->change();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to previous status enum
            $table->enum('status', ['active', 'inactive', 'suspended'])
                ->default('active')
                ->change();

            // Remove soft delete column (optional rollback)
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
