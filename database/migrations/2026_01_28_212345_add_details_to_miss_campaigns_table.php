<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miss_campaigns', function (Blueprint $table) {
            $table->foreignId('media_type_id')->nullable()->constrained('media_types');

            $table->foreignId('industry_id')->nullable()->constrained('industries');
            
            $table->mediumInteger('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');

            $table->mediumInteger('state_id')->unsigned()->nullable();
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');

            $table->mediumInteger('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('miss_campaigns', function (Blueprint $table) {
            $table->dropForeign(['media_type_id']);
            $table->dropColumn('media_type_id');

            $table->dropForeign(['industry_id']);
            $table->dropColumn('industry_id');

            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');

            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');

            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
};
