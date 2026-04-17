<?php

namespace App\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class GeographyDumpImporter
{
    private string $dumpPath;

    /**
     * Parsed INSERT statements grouped by table.
     *
     * @var array<string, array<int, string>>|null
     */
    private ?array $statementsByTable = null;

    /**
     * Tables in dependency order for inserts.
     */
    private array $insertOrder = ['regions', 'subregions', 'countries', 'states', 'cities'];

    /**
     * Tables in reverse dependency order for truncation.
     */
    private array $truncateOrder = ['cities', 'states', 'countries', 'subregions', 'regions'];

    public function __construct(?string $dumpPath = null)
    {
        $this->dumpPath = $dumpPath ?: base_path('dump_db.sql');
    }

    public function importAll(bool $truncate = true): array
    {
        $this->ensureUtf8mb4Support();

        if ($truncate) {
            $this->truncateTables($this->truncateOrder);
        }

        $imported = [];

        foreach ($this->insertOrder as $table) {
            $imported[$table] = $this->importTableStatements($table);
        }

        return $imported;
    }

    public function importTables(array $tables, bool $truncate = true): array
    {
        $this->ensureUtf8mb4Support();

        $tables = $this->normalizeTables($tables);

        if ($truncate) {
            $this->truncateTables(array_intersect($this->truncateOrder, $tables));
        }

        $imported = [];

        foreach ($this->insertOrder as $table) {
            if (! in_array($table, $tables, true)) {
                continue;
            }

            $imported[$table] = $this->importTableStatements($table);
        }

        return $imported;
    }

    public function importTable(string $table, bool $truncate = true): int
    {
        $this->ensureUtf8mb4Support();

        $table = $this->normalizeTable($table);

        if ($truncate) {
            $this->truncateTables([$table]);
        }

        return $this->importTableStatements($table);
    }

    public function availableTables(): array
    {
        return $this->insertOrder;
    }

    private function normalizeTables(array $tables): array
    {
        $normalized = [];

        foreach ($tables as $table) {
            $normalized[] = $this->normalizeTable((string) $table);
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeTable(string $table): string
    {
        $table = trim($table);

        if (! in_array($table, $this->insertOrder, true)) {
            throw new RuntimeException("Unsupported geography table: {$table}");
        }

        return $table;
    }

    private function truncateTables(array $tables): void
    {
        if ($tables === []) {
            return;
        }

        $originalForeignKeyChecks = (int) DB::selectOne('SELECT @@FOREIGN_KEY_CHECKS AS value')->value;
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            foreach ($tables as $table) {
                DB::table($table)->truncate();
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=' . $originalForeignKeyChecks . ';');
        }
    }

    private function ensureUtf8mb4Support(): void
    {
        DB::connection()->getPdo()->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');

        $database = config('database.connections.mysql.database');

        if (is_string($database) && $database !== '') {
            DB::statement(sprintf(
                'ALTER DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $database
            ));
        }

        $this->rebuildGeographySchema();
    }

    private function rebuildGeographySchema(): void
    {
        $originalForeignKeyChecks = (int) DB::selectOne('SELECT @@FOREIGN_KEY_CHECKS AS value')->value;
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            foreach ($this->truncateOrder as $table) {
                Schema::dropIfExists($table);
            }

            Schema::create('regions', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->mediumIncrements('id');
                $table->string('name', 100);
                $table->text('translations')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->useCurrent();
                $table->boolean('flag')->default(true);
                $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');
            });

            Schema::create('subregions', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->mediumIncrements('id');
                $table->string('name', 100);
                $table->text('translations')->nullable();
                $table->unsignedMediumInteger('region_id');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->useCurrent();
                $table->boolean('flag')->default(true);
                $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');

                $table->index('region_id');
                $table->foreign('region_id')->references('id')->on('regions')->onDelete('restrict');
            });

            Schema::create('countries', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
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
                $table->timestamp('updated_at')->useCurrent();
                $table->boolean('flag')->default(true);
                $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');

                $table->index('region_id', 'country_continent');
                $table->index('subregion_id', 'country_subregion');
                $table->foreign('region_id')->references('id')->on('regions')->onDelete('restrict');
                $table->foreign('subregion_id')->references('id')->on('subregions')->onDelete('restrict');
            });

            Schema::create('states', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
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
                $table->timestamp('updated_at')->useCurrent();
                $table->boolean('flag')->default(true);
                $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');
                $table->string('population')->nullable();

                $table->index('country_id', 'country_region');
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
            });

            Schema::create('cities', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
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
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->useCurrent();
                $table->boolean('flag')->default(true);
                $table->string('wikiDataId')->nullable()->comment('Rapid API GeoDB Cities');

                $table->index('state_id', 'cities_test_ibfk_1');
                $table->index('country_id', 'cities_test_ibfk_2');
                $table->foreign('state_id')->references('id')->on('states')->onDelete('restrict');
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=' . $originalForeignKeyChecks . ';');
        }
    }

    private function importTableStatements(string $table): int
    {
        $statements = $this->statementsForTable($table);

        if ($statements === []) {
            throw new RuntimeException(sprintf(
                'No INSERT statements were found for geography table "%s" in %s.',
                $table,
                $this->dumpPath
            ));
        }

        $this->runStatementsThroughMysqlClient($statements);

        return count($statements);
    }

    /**
     * Execute the import through the native mysql client when available.
     *
     * @param array<int, string> $statements
     */
    private function runStatementsThroughMysqlClient(array $statements): void
    {
        $binary = trim((string) shell_exec('command -v mysql'));

        if ($binary === '') {
            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }

            return;
        }

        $connection = config('database.connections.mysql');

        if (! is_array($connection)) {
            throw new RuntimeException('MySQL connection configuration is not available.');
        }

        $sql = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
        $sql .= implode(";\n\n", $statements) . ";\n";

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --default-character-set=utf8mb4 %s',
            escapeshellarg($binary),
            escapeshellarg((string) ($connection['host'] ?? '127.0.0.1')),
            escapeshellarg((string) ($connection['port'] ?? '3306')),
            escapeshellarg((string) ($connection['username'] ?? 'root')),
            escapeshellarg((string) ($connection['database'] ?? ''))
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $env = $_ENV;
        $env['MYSQL_PWD'] = (string) ($connection['password'] ?? '');

        $process = proc_open($command, $descriptors, $pipes, null, $env);

        if (! is_resource($process)) {
            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }

            return;
        }

        fwrite($pipes[0], $sql);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException(sprintf(
                'mysql client import failed with exit code %d%s%s',
                $exitCode,
                trim((string) $stderr) !== '' ? ': ' : '',
                trim((string) $stderr)
            ));
        }
    }

    /**
     * @return array<int, string>
     */
    private function statementsForTable(string $table): array
    {
        $this->loadStatements();

        return $this->statementsByTable[$table] ?? [];
    }

    private function loadStatements(): void
    {
        if ($this->statementsByTable !== null) {
            return;
        }

        if (! is_file($this->dumpPath)) {
            throw new RuntimeException("Geography dump file not found: {$this->dumpPath}");
        }

        $contents = file_get_contents($this->dumpPath);

        if ($contents === false) {
            throw new RuntimeException("Unable to read geography dump: {$this->dumpPath}");
        }

        if ($contents === '') {
            throw new RuntimeException("Geography dump file is empty: {$this->dumpPath}");
        }

        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
        $statementsByTable = [];
        $lines = preg_split('/\R/', $contents) ?: [];
        $headerPattern = '/^\s*INSERT\s+(?:IGNORE\s+)?INTO\s+(?:`[^`]+`\.)?`?(regions|subregions|countries|states|cities)`?\b/i';

        $currentTable = null;
        $currentStatement = [];
        $insertCount = 0;

        foreach ($lines as $line) {
            if ($currentTable === null) {
                if (! preg_match($headerPattern, $line, $matches)) {
                    continue;
                }

                $currentTable = strtolower($matches[1]);
                $currentStatement = [$line];

                if (str_ends_with(rtrim($line), ';')) {
                    $statementsByTable[$currentTable][] = implode("\n", $currentStatement);
                    $insertCount++;
                    $currentTable = null;
                    $currentStatement = [];
                }

                continue;
            }

            $currentStatement[] = $line;

            if (! str_ends_with(rtrim($line), ';')) {
                continue;
            }

            $statementsByTable[$currentTable][] = implode("\n", $currentStatement);
            $insertCount++;
            $currentTable = null;
            $currentStatement = [];
        }

        if ($currentTable !== null && $currentStatement !== []) {
            throw new RuntimeException(sprintf(
                'Geography dump ended while reading an INSERT statement for table "%s" in %s.',
                $currentTable,
                $this->dumpPath
            ));
        }

        if ($insertCount === 0) {
            throw new RuntimeException("No INSERT statements were found in {$this->dumpPath}.");
        }

        $this->statementsByTable = $statementsByTable;
    }
}
