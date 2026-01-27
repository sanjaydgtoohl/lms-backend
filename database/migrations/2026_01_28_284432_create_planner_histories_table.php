<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planner_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Relation with planner
            $table->foreignId('planner_id')
                ->constrained('planners')
                ->onDelete('cascade');

            // Snapshot of planner data
            $table->foreignId('brief_id')->constrained('briefs');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('planner_status_id')
                ->nullable()
                ->constrained('planner_statuses')
                ->onDelete('restrict');

            $table->json('submitted_plan')->nullable();
            $table->string('backup_plan')->nullable();

            $table->enum('status', ['1', '2', '15'])
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('planner_id');
            $table->index('planner_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planner_histories');
    }
};
