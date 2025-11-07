<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('slug')->nullable()->after('display_name');
            $table->enum('status', ['1', '2', '15'])
                  ->default('1')
                  ->comment('1 = active, 2 = deactivated, 15 = user soft delete')
                  ->after('description');
                  
            $table->softDeletes(); // <-- Re-added this line
        });

        // Populate unique data for existing rows
        $roles = DB::table('roles')->whereNull('uuid')->get();

        foreach ($roles as $role) {
            DB::table('roles')->where('id', $role->id)->update([
                'uuid' => (string) Str::uuid(),
                'slug' => Str::slug($role->display_name ?? $role->name) . '-' . $role->id 
            ]);
        }

        // Apply unique constraints
        Schema::table('roles', function (Blueprint $table) {
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
        Schema::table('roles', function (Blueprint $table) {
            
            // Drop unique keys first
            $table->dropUnique('roles_uuid_unique');
            $table->dropUnique('roles_slug_unique');
            
            // Drop columns
            $table->dropSoftDeletes(); // <-- Re-added this line
            $table->dropColumn(['uuid', 'slug', 'status']);
        });
    }
};