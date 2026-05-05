<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organisations Table Migration
 * -----------------------------------------
 * This migration creates the organisations table with fields for
 * name, slug, status, timestamps, and soft delete functionality.
 *
 * @package Database\Migrations
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->enum('status', ['1', '2', '15'])
                ->default('2')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
