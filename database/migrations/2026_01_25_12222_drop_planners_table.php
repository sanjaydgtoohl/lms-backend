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
        Schema::dropIfExists('planners');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::createIfNotExists('planners', function (Blueprint $table) {
            // Primary and unique identifiers
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Relationships
            $table->foreignId('brief_id')->constrained('briefs')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('planner_status_id')->nullable()->constrained('planner_statuses')->onDelete('restrict');
           
            // Status field
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted');

            // File storage for plans
            $table->json('submitted_plan')->nullable()
                ->comment('JSON array containing up to 2 submitted plan file paths/references');
            $table->string('backup_plan')->nullable()
                ->comment('File path or reference for backup plan (1 file)');

            // System fields
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('brief_id');
            $table->index('created_by');
            $table->index('planner_status_id');
            $table->index('status');
            $table->index('created_at');
        });
    }
};
