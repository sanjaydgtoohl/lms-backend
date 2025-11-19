<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns (safe)
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'url')) {
                $table->string('url')->nullable()->after('slug');
            }
            
            if (!Schema::hasColumn('permissions', 'icon_file')) {
                $table->string('icon_file')->nullable()->after('url');
            }
            
            if (!Schema::hasColumn('permissions', 'icon_text')) {
                $table->string('icon_text')->nullable()->after('icon_file');
            }
        });

        // Step 2: Modify is_parent type (MUST be done alone)
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('is_parent')->nullable()->change();
        });

        // Step 3: Add foreign key (separate call)
        Schema::table('permissions', function (Blueprint $table) {

            // Check if FK already exists
            try {
                $table->foreign('is_parent')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('set null');
            } catch (\Exception $e) {
                // Ignore if FK already exists
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['is_parent']);
            
            $table->tinyInteger('is_parent')
                ->default(0)
                ->change();

            $table->dropColumn(['url', 'icon_file', 'icon_text']);
        });
    }
};
