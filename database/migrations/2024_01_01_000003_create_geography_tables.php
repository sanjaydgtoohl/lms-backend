<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createTableIfMissing('regions', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name', 100);
            $table->text('translations')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('flag')->default(true);
            $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');
        });

        $this->createTableIfMissing('subregions', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name', 100);
            $table->text('translations')->nullable();
            $table->unsignedMediumInteger('region_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('flag')->default(true);
            $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');

            $table->index('region_id');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('restrict');
        });

        $this->createTableIfMissing('countries', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name', 100);
            $table->char('iso3', 3)->nullable();
            $table->char('numeric_code', 3)->nullable();
            $table->char('iso2', 2)->nullable();
            $table->string('phonecode')->nullable();
            $table->string('capital')->nullable();
            $table->string('currency')->nullable();
            $table->string('currency_name')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('tld')->nullable();
            $table->string('native')->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->unsignedBigInteger('gdp')->nullable();
            $table->string('region')->nullable();
            $table->unsignedMediumInteger('region_id')->nullable();
            $table->string('subregion')->nullable();
            $table->unsignedMediumInteger('subregion_id')->nullable();
            $table->string('nationality')->nullable();
            $table->text('timezones')->nullable();
            $table->text('translations')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('emoji', 191)->nullable();
            $table->string('emojiU', 191)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('flag')->default(true);
            $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');

            $table->index('region_id', 'country_continent');
            $table->index('subregion_id', 'country_subregion');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('restrict');
            $table->foreign('subregion_id')->references('id')->on('subregions')->onDelete('restrict');
        });

        $this->createTableIfMissing('states', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name');
            $table->unsignedMediumInteger('country_id');
            $table->char('country_code', 2);
            $table->string('fips_code')->nullable();
            $table->string('iso2')->nullable();
            $table->string('iso3166_2', 10)->nullable();
            $table->string('type', 191)->nullable();
            $table->integer('level')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('native')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('timezone')->nullable()->comment('IANA timezone identifier (e.g., America/New_York)');
            $table->text('translations')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('flag')->default(true);
            $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');
            $table->string('population')->nullable();

            $table->index('country_id', 'country_region');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
        });

        $this->createTableIfMissing('cities', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('name');
            $table->unsignedMediumInteger('state_id');
            $table->string('state_code');
            $table->unsignedMediumInteger('country_id');
            $table->char('country_code', 2);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('native')->nullable();
            $table->string('timezone')->nullable()->comment('IANA timezone identifier (e.g., America/New_York)');
            $table->text('translations')->nullable();
            $table->timestamp('created_at')->default('2014-01-01 06:31:01');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->boolean('flag')->default(true);
            $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');

            $table->index('state_id', 'cities_test_ibfk_1');
            $table->index('country_id', 'cities_test_ibfk_2');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('restrict');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('subregions');
        Schema::dropIfExists('regions');
    }

    private function createTableIfMissing(string $tableName, \Closure $callback): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($callback): void {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $callback($table);
        });
    }
};
