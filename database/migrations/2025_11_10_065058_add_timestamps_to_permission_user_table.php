<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('permission_user', function (Blueprint $table) {
        $table->timestamps(); // Add this line
    });
}

// Also add the 'down' method for good practice
public function down(): void
{
    Schema::table('permission_user', function (Blueprint $table) {
        $table->dropTimestamps(); // Add this line
    });
}
};
