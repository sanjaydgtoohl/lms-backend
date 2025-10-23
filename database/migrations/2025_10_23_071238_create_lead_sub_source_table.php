<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadSubSourceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lead_sub_source', function (Blueprint $table) {
            $table->id();

            // --- Foreign Key Connection ---
            $table->unsignedBigInteger('lead_source_id');
            // ------------------------------
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = user soft delete');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lead_source_id')
                ->references('id')
                ->on('lead_source')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_sub_source');
    }
};
