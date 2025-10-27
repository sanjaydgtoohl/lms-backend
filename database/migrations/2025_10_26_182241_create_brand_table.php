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
        Schema::create('brand', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = user soft delete');
            $table->unsignedBigInteger('contact_person_id')->nullable();
            $table->foreignId('brand_type_id')->constrained('brand_types'); // <-- SAHI
            $table->string('website')->nullable();
            $table->string('postal_code')->nullable();
            $table->foreignId('agency_id')->nullable()->constrained('agencies');
            $table->foreignId('industry_id')->constrained('industries');
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->foreginId('zone_id')->nullable()->constrained('zones');
            //Which User create brand...
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->timestamps();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand');
    }
};
