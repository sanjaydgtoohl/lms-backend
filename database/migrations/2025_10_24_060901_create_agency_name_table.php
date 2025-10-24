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
        Schema::create('agency_name', function (Blueprint $table) {
            $table->id(); 
            $table->string('name');
            $table->string('slug');
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = user soft delete');
            $table->foreignId('agency_group_id')
                  ->nullable()                   
                  ->constrained('agency_groups') 
                  ->onDelete('set null');
            $table->foreignId('agency_type_id')
                  ->constrained('agency_type') 
                  ->onDelete('cascade');
            $table->foreignId('agency_brand_id')
                  ->nullable()
                  ->constrained('agency_brand')
                  ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_name');
    }
};