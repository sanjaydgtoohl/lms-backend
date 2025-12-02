<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('briefs');
    }

    public function down(): void
    {
        // Optional: recreate empty table if rollback happens
        Schema::create('briefs', function ($table) {
            $table->id();
            // Add fields again if needed
            $table->timestamps();
        });
    }
};
