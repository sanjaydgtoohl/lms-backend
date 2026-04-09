<?php

/**
 * Update Users Email Unique Index Migration
 * -----------------------------------------
 * Modifies the users table to enforce unique email addresses for active users while allowing
 * soft-deleted users to reuse emails through a generated column approach.
 *
 * @package Database\Migrations
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-01-28
 */

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

            // Add generated column for active email uniqueness (MySQL 8+ workaround)
            // This column is email when deleted_at IS NULL, NULL otherwise
            $table->string('email_active')->virtualAs('IF(deleted_at IS NULL, email, NULL)')->nullable();

            // Create unique index on the generated column to enforce uniqueness for active users
            $table->unique('email_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop unique index on generated column
            $table->dropUnique(['email_active']);

            // Drop the generated column
            $table->dropColumn('email_active');

            // Restore original unique index on email
            $table->unique('email');
        });
    }
};
