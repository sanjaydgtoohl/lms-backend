<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDesignationsTable extends Migration
{
    public function up()
    {
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['1', '2', '15'])
                ->default('1')
                ->comment('1 = active, 2 = deactivated, 15 = user soft delete');
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('designations');
    }
}
