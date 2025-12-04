<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->before('current_assign_user');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Drop foreign key manually
            $table->dropForeign(['created_by']);

            // Drop column
            $table->dropColumn('created_by');
        });
    }
};
