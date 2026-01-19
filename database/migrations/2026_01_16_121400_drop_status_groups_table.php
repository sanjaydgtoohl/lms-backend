<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('status_groups');
    }

    public function down(): void
    {
        Schema::create('status_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('slug', 191);
            $table->json('status_id');
            $table->enum('status', ['1', '2', '15'])->default('1')
                  ->comment('1 = active, 2 = deactivated, 15 = user soft delete');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
};
