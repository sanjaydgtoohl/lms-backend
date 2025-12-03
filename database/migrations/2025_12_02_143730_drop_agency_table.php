<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permanently drop the table.
     */
    public function up(): void
    {
        Schema::dropIfExists('agency_groups');
    }

    /**
     * Nothing on rollback because delete is permanent.
     */
    public function down(): void
    {
        // intentionally left empty (permanent delete)
    }
};