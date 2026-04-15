<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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

    private function importTableStatements(string $table): int
    {
        $count = 0;

        foreach ($this->statementsForTable($table) as $statement) {
            DB::unprepared($statement);
            $count++;
        }

        return $count;
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

        $handle = fopen($this->dumpPath, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open geography dump: {$this->dumpPath}");
        }

        $statementsByTable = [];
        $allowedTables = array_flip($this->insertOrder);
        $buffer = '';
        $collecting = false;
        $currentTable = null;

        while (($line = fgets($handle)) !== false) {
            if (! $collecting) {
                if (preg_match('/^INSERT INTO `([^`]+)`/', $line, $matches) && isset($allowedTables[$matches[1]])) {
                    $collecting = true;
                    $currentTable = $matches[1];
                    $buffer = $line;
                }

                continue;
            }

            $buffer .= $line;

            if (str_ends_with(rtrim($line), ';')) {
                $statementsByTable[$currentTable][] = $buffer;
                $buffer = '';
                $collecting = false;
                $currentTable = null;
            }
        }

        fclose($handle);

        $this->statementsByTable = $statementsByTable;
    }
}
