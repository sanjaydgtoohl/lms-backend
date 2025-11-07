<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Required
use Illuminate\Support\Str; // <-- Required

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Add the new columns (nullable for now)
        Schema::table('permissions', function (Blueprint $table) {
            
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('slug')->nullable()->after('display_name');
            
            $table->enum('status', ['1', '2', '15'])
                  ->default('1')
                  ->comment('1 = active, 2 = deactivated, 15 = user soft delete')
                  ->after('description');
                  
            $table->softDeletes(); // <-- Add soft deletes
        });

        // 2. Populate unique data for existing rows
        $permissions = DB::table('permissions')->whereNull('uuid')->get();

        foreach ($permissions as $permission) {
            DB::table('permissions')->where('id', $permission->id)->update([
                'uuid' => (string) Str::uuid(),
                // Create a unique slug based on name/display_name and ID
                'slug' => Str::slug($permission->display_name ?? $permission->name) . '-' . $permission->id 
            ]);
        }

        // 3. Apply unique constraints now that columns are populated
        Schema::table('permissions', function (Blueprint $table) {
            $table->unique('uuid');
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            
            // Drop unique keys first
            $table->dropUnique('permissions_uuid_unique');
            $table->dropUnique('permissions_slug_unique');
            
            // Drop columns
            $table->dropSoftDeletes();
            $table->dropColumn(['uuid', 'slug', 'status']);
        });
    }
};