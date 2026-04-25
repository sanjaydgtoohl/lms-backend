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
            $table->unsignedBigInteger('assign_by')->nullable()->after('status');
            $table->foreign('assign_by')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('assign_to')->nullable()->after('assign_by');
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.    php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::table('miss_campaigns', function (Blueprint $table) {
            $table->dropForeign(['assign_by']);
            $table->dropColumn('assign_by');

            $table->dropForeign(['assign_to']);
            $table->dropColumn('assign_to');
        });
    }
};
