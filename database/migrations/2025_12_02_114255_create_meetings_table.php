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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title')->comment('Meeting title');
            $table->string('slug');
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('attendees_id')->nullable();
            $table->enum('type', ['face_to_face', 'online'])
                ->default('online')
                ->comment('face_to_face = In-person meeting, online = Virtual meeting');
            $table->string('location')->nullable()->comment('Meeting location or venue');
            $table->text('agenda')->nullable()->comment('Meeting agenda or topics to discuss');
            $table->string('link')->nullable()->comment('Meeting link for online meetings or reference');
            $table->date('meeting_date')->nullable()->comment('Future date of the meeting');
            $table->time('meeting_time')->nullable()->comment('Future time of the meeting');
            
            $table->enum('status', ['1', '2', '15'])
                ->default('2')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->onDelete('cascade');
            
            $table->foreign('attendees_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
