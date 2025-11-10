<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_add_timestamps_to_role_user_table.php

public function up(): void
{
    Schema::table('role_user', function (Blueprint $table) {
        // ADD THIS LINE
        $table->timestamps(); 
    });
}

// (Optional but good practice: add the reverse)
public function down(): void
{
    Schema::table('role_user', function (Blueprint $table) {
        // ADD THIS LINE
        $table->dropTimestamps(); 
    });
}
};
