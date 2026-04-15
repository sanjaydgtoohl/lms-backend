<?php

namespace Database\Seeders;

use App\Services\GeographyDumpImporter;
use Illuminate\Database\Seeder;

abstract class GeoLocationTableSeeder extends Seeder
{
    abstract protected function table(): string;

    public function run(): void
    {
        app(GeographyDumpImporter::class)->importTable($this->table());
    }
}
