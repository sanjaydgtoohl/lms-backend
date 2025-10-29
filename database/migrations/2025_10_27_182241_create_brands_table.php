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

            // Optional contact person (user)
            $table->foreignId('contact_person_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Core relationships
            $table->foreignId('brand_type_id')->constrained('brand_types')->cascadeOnDelete();
            $table->foreignId('industry_id')->constrained('industries')->cascadeOnDelete();
            
            // ✅ Correct country foreign key (use country_id instead of id)
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();

            // Optional location data
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('subregion_id')->nullable()->constrained('subregions')->nullOnDelete();

            // Optional agency and creator
            $table->foreignId('agency_id')->nullable()->constrained('agency')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Additional info
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