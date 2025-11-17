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
        Schema::create('lead_assign_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Foreign keys + indexes
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('assign_user_id');
            $table->unsignedBigInteger('current_user_id');
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->unsignedBigInteger('lead_status_id')->nullable();
            $table->unsignedBigInteger('call_status_id')->nullable();

            // Enum Status
            $table->enum('status', ['1', '2', '15'])
                  ->default('1')
                  ->comment('1 = active, 2 = deactivated, 15 = soft deleted');

            $table->timestamps();
            $table->softDeletes();

            // ======== FOREIGN KEYS ========
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');

            $table->foreign('assign_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('current_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('priority_id')
                ->references('id')
                ->on('priorities')
                ->onDelete('set null');

            $table->foreign('lead_status_id')
                ->references('id')
                ->on('lead_statuses')
                ->onDelete('set null');

            $table->foreign('call_status_id')
                ->references('id')
                ->on('call_statuses')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_assign_histories');
    }
};
