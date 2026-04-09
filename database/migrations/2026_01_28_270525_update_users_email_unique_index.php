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
        Schema::table('users', function (Blueprint $table) {
            // Drop existing unique index on email
            $table->dropUnique(['email']);

            // Create composite unique index on email and deleted_at
            // This allows soft-deleted users to reuse emails while keeping active emails unique
            $table->unique(['email', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop composite unique index
            $table->dropUnique(['email', 'deleted_at']);

            // Restore original unique index on email
            $table->unique('email');
        });
    }
};
