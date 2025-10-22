<?php
// highlight-line
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndustriesTable extends Migration
{
    public function up()
    {
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('industry_name');
            $table->timestamps(); // Date & Time
            $table->softDeletes(); // Soft delete 
        });
    }

    public function down()
    {
        Schema::dropIfExists('industries');
    }
}