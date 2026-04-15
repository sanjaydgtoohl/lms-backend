<?php

namespace App\Console\Commands;

use App\Services\GeographyDumpImporter;
use Illuminate\Console\Command;

class ImportGeographyDump extends Command
{
    protected $signature = 'geography:import-dump {--table=* : Import only the selected tables} {--no-truncate : Keep existing rows instead of truncating first}';

    protected $description = 'Import geography reference data from dump_db.sql';

    public function handle(GeographyDumpImporter $importer): int
    {
        $tables = $this->option('table');
        $truncate = ! $this->option('no-truncate');

        if (empty($tables)) {
            $imported = $importer->importAll($truncate);
        } else {
            $imported = $importer->importTables($tables, $truncate);
        }

        foreach ($imported as $table => $count) {
            $this->line(sprintf('%s: imported %d statement(s)', $table, $count));
        }

        $this->info('Geography dump import complete.');

        return self::SUCCESS;
    }
}
