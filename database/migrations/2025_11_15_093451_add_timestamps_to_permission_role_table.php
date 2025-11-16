<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToPermissionRoleTable extends Migration
{
    public function up()
    {
        Schema::table('permission_role', function (Blueprint $table) {
            if (!Schema::hasColumn('permission_role', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down()
    {
        Schema::table('permission_role', function (Blueprint $table) {
            if (Schema::hasColumn('permission_role', 'created_at')) {
                $table->dropColumn('created_at');
                $table->dropColumn('updated_at');
            }
        });
    }
}
