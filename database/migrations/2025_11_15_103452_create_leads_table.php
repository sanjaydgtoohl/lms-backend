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
            $table->foreignId('agency_id')->nullable()->constrained('agency'); // FIXED
            $table->foreignId('current_assign_user')->nullable()->constrained('users');
            $table->foreignId('priority_id')->nullable()->constrained('priorities');

            // FIX: unsignedBigInteger cannot use "constrained"
            $table->unsignedBigInteger('call_status')->nullable();
            $table->foreign('call_status')->references('id')->on('call_statuses');

            // lead_status: tell me if this should be linked to a table
            $table->unsignedBigInteger('lead_status')->nullable();

            // Lead main info
            $table->string('name')->nullable();
            $table->string('profile_url')->nullable();
            $table->string('email')->nullable();
            $table->json('mobile_number')->nullable();

            // ENUM Type
            $table->enum('type', ['Agency', 'Brand'])->nullable();

            // More FKs (all corrected)
            $table->foreignId('designation_id')->nullable()->constrained('designations');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('sub_source_id')->nullable()->constrained('lead_sub_source');
            $table->mediumInteger('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            $table->mediumInteger('state_id')->unsigned()->nullable();
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');

            $table->mediumInteger('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');

            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->foreignId('statuses')->nullable()->constrained('statuses');

            // address
            $table->string('postal_code')->nullable();

            // comment
            $table->text('comment')->nullable();

            // lead status enum
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
