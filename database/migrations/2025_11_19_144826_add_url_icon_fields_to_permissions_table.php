<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new columns
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

        // Step 2: Modify is_parent type
        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('is_parent')->nullable()->change();
        });

        // Step 3: Fix invalid data before adding foreign key
        DB::statement("
            UPDATE permissions 
            SET is_parent = NULL
            WHERE is_parent IS NOT NULL
            AND is_parent NOT IN (SELECT id FROM (SELECT id FROM permissions) AS temp)
        ");

        // Step 4: Add FK constraint
        Schema::table('permissions', function (Blueprint $table) {
            // Only add FK if not exists
            $table->foreign('is_parent')
                ->references('id')
                ->on('permissions')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['is_parent']);

            $table->tinyInteger('is_parent')->default(0)->change();

            $table->dropColumn(['url', 'icon_file', 'icon_text']);
        });
    }
};
