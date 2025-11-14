<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_contacts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug');

            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')
                  ->references('id')
                  ->on('leads')
                  ->onDelete('cascade');

            $table->string('full_name');
            $table->string('profile_url')->nullable();
            $table->string('email')->nullable();
            
            $table->string('mobile_number', 20); // Required
            $table->string('mobile_number_optional', 20)->nullable(); // Optional

            $table->string('type', 50)->nullable(); 

            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('sub_source_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();

            $table->string('postal_code', 20)->nullable();
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = user soft delete');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_contacts');
    }
};