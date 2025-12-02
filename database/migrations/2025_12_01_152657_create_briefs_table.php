<?php

use App\Traits\SoftDeletes;
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
        Schema::create('briefs', function (Blueprint $table) {
            // Primary and unique identifiers
            $table->id();
            $table->uuid('uuid')->unique();

            // Brief details
            $table->string('name');
            $table->string('slug');
            $table->string('product_name')->nullable();

            // Relationships
            $table->foreignId('contact_person_id')->constrained('leads');
            $table->foreignId('brand_id')->nullable()->constrained('brands');
            $table->foreignId('agency_id')->nullable()->constrained('agency');

            // Campaign details
            $table->enum('mode_of_campaign', ['programmatic', 'non_programmatic'])->nullable();
            $table->string('media_type')->nullable()->comment('Type of media for the campaign');
            $table->decimal('budget', 15, 2)->nullable()->comment('Campaign budget amount');

            // Assignment and workflow
            $table->foreignId('assign_user_id')->nullable()->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('brief_status_id')->nullable()->constrained('brief_statuses');
            $table->foreignId('priority_id')->nullable()->constrained('priorities');

            // Additional information
            $table->text('comment')->nullable();
            $table->dateTime('submission_date')->nullable();

            // System fields
            $table->enum('status', ['1', '2', '15'])
                ->default('2')
                ->comment('1 = active, 2 = deactivated, 15 = soft deleted');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('briefs');
    }
};
