<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('brand_id')->nullable()->constrained('brands');
            $table->foreignId('agency_id')->nullable()->constrained('agencies');
            $table->foreignId('current_assign_user')->nullable()->constrained('users');
            $table->foreignId('priority_id')->nullable()->constrained('priorities');

            // JSON fields
            $table->unsignedBigInteger('call_status')->nullable()->constrained('call_statuses');
            $table->unsignedBigInteger('lead_status')->nullable(); // If FK, tell me table

            // Lead main info
            $table->string('name')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('email')->nullable();
            $table->json('mobile_number')->nullable();

            // ENUM Type
            $table->enum('type', ['Agency', 'Brand'])->nullable();

            // More FKs
            $table->foreignId('designation_id')->nullable()->constrained('designations');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('sub_source_id')->nullable()->constrained('lead_sub_source');
            $table->foreignId('country_id')->nullable()->constrained('countries');
            $table->foreignId('state_id')->nullable()->constrained('states');
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->foreignId('statuses')->nullable()->constrained('statuses');
            // address
            $table->string('postal_code')->nullable();

            // comment
            $table->text('comment')->nullable();

            // lead status
            $table->enum('status', ['1', '2', '15'])
                ->default('2')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};