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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('model', 100);
            $table->unsignedBigInteger('model_id');
            $table->string('action', 50);
            $table->text('description')->nullable();

            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();

            // Foreign Keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // Indexes
            $table->index(['model', 'model_id']);
            $table->index('user_id');
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
        Schema::dropIfExists('activity_logs');
    }
};
