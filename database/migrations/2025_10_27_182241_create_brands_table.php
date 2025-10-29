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

            // Optional contact person (usually a user)
            $table->foreignId('contact_person_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Core relationships
            $table->foreignId('brand_type_id')->constrained('brand_types');
            $table->foreignId('industry_id')->constrained('industries');
            $table->foreignId('country_id')->constrained('countries');

            // Optional location data
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->foreignId('region_id')->nullable()->constrained('regions');
            $table->foreignId('subregions_id')->nullable()->constrained('subregions');

            // Optional agency and creator
            $table->foreignId('agency_id')->nullable()->constrained('agency');
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