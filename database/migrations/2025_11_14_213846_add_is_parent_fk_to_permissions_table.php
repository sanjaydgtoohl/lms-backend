<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Remove old column if exists
            if (Schema::hasColumn('permissions', 'is_parent')) {
                $table->dropColumn('is_parent');
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            // Add bigint foreign key column
            $table->unsignedBigInteger('is_parent')->nullable()->after('id');

            $table->foreign('is_parent')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'is_parent')) {
                $table->dropForeign(['is_parent']);
                $table->dropColumn('is_parent');
            }
        });
    }
};
