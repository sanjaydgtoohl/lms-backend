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
        Schema::create('countries', function (Blueprint $table) { 
            $table->unsignedBigInteger('id')->primary(); 
            $table->string('name');
            $table->string('slug')->unique();
            
            $table->string('iso2', 2)->nullable()->index()->comment('e.g., IN, US');

            $table->enum('status', ['1', '2', '15'])
                  ->default('1')
                  ->comment('1 = active, 2 = deactivated, 15 = user soft delete');

            $table->timestamps(); // created_at aur updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};

