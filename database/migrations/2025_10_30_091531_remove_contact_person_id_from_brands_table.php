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
        Schema::table('brands', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['contact_person_id']);

            // Then drop the column
            $table->dropColumn('contact_person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->foreignId('contact_person_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }
};
