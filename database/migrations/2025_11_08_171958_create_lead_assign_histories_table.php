<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_assign_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('assign_user_id')->nullable();
            $table->unsignedBigInteger('current_user_id')->nullable();
            $table->unsignedBigInteger('priority_id')->nullable();
            $table->unsignedBigInteger('lead_status_id')->nullable();
            $table->unsignedBigInteger('call_status_id')->nullable();
            $table->enum('status', ['1', '2', '15'])->default('1');
            $table->timestamps();
            $table->softDeletes();

            $table->index('lead_id');
            $table->index('assign_user_id');
            $table->index('current_user_id');
            $table->index('priority_id');
            $table->index('lead_status_id');
            $table->index('call_status_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_assign_histories');
    }
};
