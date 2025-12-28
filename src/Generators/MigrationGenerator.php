<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Support\Str;
use Mosweed\CrudGenerator\Support\FieldParser;

class MigrationGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $stub = $this->getStub('migration');
        $tableName = $this->getTableName();
        $timestamp = date('Y_m_d_His');
        
        $content = $this->replaceStubVariables($stub, [
            'table_name' => $tableName,
            'class_name' => 'Create' . Str::studly($tableName) . 'Table',
            'columns' => $this->buildColumns(),
            'foreign_keys' => $this->buildForeignKeys(),
        ]);

        $filename = "{$timestamp}_create_{$tableName}_table.php";
        $path = config('crud-generator.paths.migration') . '/' . $filename;

        return $this->putFile($path, $content);
    }

    protected function buildColumns(): string
    {
        $columns = [];

        foreach ($this->fields as $field) {
            $columns[] = '            ' . FieldParser::toMigrationColumn($field);
        }

        // Voeg foreign key columns toe voor belongsTo relaties
        foreach ($this->relations as $relation) {
            if ($relation['type'] === 'belongsTo') {
                $foreignKey = $relation['foreign_key'] ?? Str::snake($relation['model']) . '_id';
                
                // Check of deze foreign key niet al in de velden zit
                $exists = collect($this->fields)->contains(fn($f) => $f['name'] === $foreignKey);
                
                if (!$exists) {
                    $columns[] = "            \$table->foreignId('{$foreignKey}')->constrained()->cascadeOnDelete();";
                }
            }
        }

        return implode("\n", $columns);
    }

    protected function buildForeignKeys(): string
    {
        $foreignKeys = [];

        foreach ($this->relations as $relation) {
            if ($relation['type'] === 'belongsToMany') {
                // Pivot table wordt apart gegenereerd
                continue;
            }
        }

        return implode("\n", $foreignKeys);
    }

    /**
     * Genereer pivot table migration voor belongsToMany relaties.
     */
    public function generatePivotTable(string $model1, string $model2): bool
    {
        $table1 = Str::snake($model1);
        $table2 = Str::snake($model2);
        
        // Alphabetische volgorde voor consistentie
        $tables = [$table1, $table2];
        sort($tables);
        $pivotTableName = implode('_', $tables);

        $stub = $this->getStub('migration.pivot');
        $timestamp = date('Y_m_d_His', strtotime('+1 second'));

        $content = $this->replaceStubVariables($stub, [
            'table_name' => $pivotTableName,
            'class_name' => 'Create' . Str::studly($pivotTableName) . 'Table',
            'foreign_key_1' => $tables[0] . '_id',
            'foreign_key_2' => $tables[1] . '_id',
            'table_1' => Str::pluralStudly($tables[0]),
            'table_2' => Str::pluralStudly($tables[1]),
        ]);

        $filename = "{$timestamp}_create_{$pivotTableName}_table.php";
        $path = config('crud-generator.paths.migration') . '/' . $filename;

        return $this->putFile($path, $content);
    }
}
