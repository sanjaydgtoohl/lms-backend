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
        Schema::create('miss_campaign_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('miss_campaign_id');
            $table->unsignedBigInteger('assign_by')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->text('comment')->nullable();
            $table->string('status');
            $table->timestamps();
            
            $table->foreign('miss_campaign_id')->references('id')->on('miss_campaigns')->onDelete('cascade');
            $table->foreign('assign_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miss_campaign_histories');
    }
};
