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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            // Optional contact person
            // $table->foreignId('contact_person_id')
            //     ->nullable()
            //     ->constrained('users')
            //     ->nullOnDelete();

            // Core relationships
            $table->foreignId('brand_type_id')->constrained('brand_types');
            $table->foreignId('industry_id')->constrained('industries');
            
            // Correct country reference — must match countries.id type
            $table->unsignedInteger('country_id');

            // Optional location data
            $table->unsignedInteger('state_id');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('region_id');
            $table->unsignedInteger('subregion_id');

            // Option creator
            
            $table->foreignId('created_by')->nullable()->constrained('users');

            // Brand info
            $table->string('website')->nullable();
            $table->string('postal_code')->nullable();

            // Status flags
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = user soft delete');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};