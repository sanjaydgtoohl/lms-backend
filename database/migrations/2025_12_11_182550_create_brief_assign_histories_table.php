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
        Schema::create('brief_assign_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('brief_id');
            $table->unsignedBigInteger('assign_by_id');
            $table->unsignedBigInteger('assign_to_id');
            $table->unsignedBigInteger('brief_status_id')->nullable();
            $table->dateTime('brief_status_time')->nullable()->comment('brief updated time');
            $table->dateTime('submission_date')->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['1', '2', '15'])
                ->default('2')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted');
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('brief_id')
            ->references('id')
            ->on('briefs')
            ->onDelete('cascade');

            $table->foreign('assign_by_id')
            ->references('id')
            ->on('users')
            ->onDelete('restrict');

            $table->foreign('assign_to_id')
            ->references('id')
            ->on('users')
            ->onDelete('restrict');

            $table->foreign('brief_status_id')
            ->references('id')
            ->on('brief_statuses')
            ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brief_assign_histories');
    }
};