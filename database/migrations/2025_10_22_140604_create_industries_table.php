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
            $table->string('name');
            $table->string('slug');
            $table->enum('status',[1,2,15])->deflut(1)->comment('1 is active 2 is deaction and 15 is user doft delete');
            $table->timestamps(); // Date & Time
            $table->softDeletes(); // Soft delete 
        });
    }

    public function down()
    {
        Schema::dropIfExists('industries');
    }
}