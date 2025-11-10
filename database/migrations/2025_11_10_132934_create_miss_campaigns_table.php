<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('miss_campaigns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug');
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = user soft delete');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('lead_source_id');
            $table->unsignedBigInteger('lead_sub_source_id')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Uncomment if you want FK constraints
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('lead_source_id')->references('id')->on('lead_sources')->onDelete('cascade');
            $table->foreign('lead_sub_source_id')->references('id')->on('lead_sub_sources')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('miss_campaigns');
    }
};
