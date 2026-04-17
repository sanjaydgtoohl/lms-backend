<?php

namespace Database\Seeders;

use App\Services\GeographyDumpImporter;
use Illuminate\Database\Seeder;
use RuntimeException;

abstract class GeoLocationTableSeeder extends Seeder
{
    abstract protected function table(): string;

    public function run(): void
    {
        $importer = new GeographyDumpImporter();
        $count = $importer->importTable($this->table());

        if ($count === 0) {
            throw new RuntimeException(sprintf(
                'No geography rows were imported for table "%s". Check dump_db.sql and the import parser.',
                $this->table()
            ));
        }
    }
}
