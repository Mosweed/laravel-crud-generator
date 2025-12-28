<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Support\Str;

class ModelGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $stub = $this->getStub('model');

        $content = $this->replaceStubVariables($stub, [
            'namespace' => $this->modelNamespace,
            'class_name' => $this->modelName,
            'table_name' => $this->getTableName(),
            'fillable' => $this->buildFillable(),
            'casts' => $this->buildCasts(),
            'relations' => $this->buildRelations(),
            'traits' => $this->buildTraits(),
            'imports' => $this->buildImports(),
        ]);

        $path = config('crud-generator.paths.model') . '/' . $this->modelName . '.php';

        return $this->putFile($path, $content);
    }

    protected function buildFillable(): string
    {
        $fillable = array_map(fn($field) => "'{$field['name']}'", $this->fields);

        // Voeg foreign keys toe
        foreach ($this->relations as $relation) {
            if ($relation['type'] === 'belongsTo') {
                $foreignKey = $relation['foreign_key'] ?? Str::snake($relation['model']) . '_id';
                if (!in_array("'{$foreignKey}'", $fillable)) {
                    $fillable[] = "'{$foreignKey}'";
                }
            }
        }

        return implode(",\n        ", $fillable);
    }

    protected function buildCasts(): string
    {
        $casts = [];

        foreach ($this->fields as $field) {
            $cast = match ($field['type']) {
                'boolean' => "'{$field['name']}' => 'boolean'",
                'integer', 'bigInteger' => "'{$field['name']}' => 'integer'",
                'float' => "'{$field['name']}' => 'float'",
                'decimal' => "'{$field['name']}' => 'decimal:2'",
                'date' => "'{$field['name']}' => 'date'",
                'datetime', 'timestamp' => "'{$field['name']}' => 'datetime'",
                'json' => "'{$field['name']}' => 'array'",
                'enum' => "'{$field['name']}' => 'string'",
                default => null,
            };

            if ($cast) {
                $casts[] = $cast;
            }
        }

        return implode(",\n        ", $casts);
    }

    protected function buildRelations(): string
    {
        $relations = [];

        foreach ($this->relations as $relation) {
            $methodName = $this->getRelationMethodName($relation);
            $relatedModel = $relation['model'];
            $foreignKey = $relation['foreign_key'] ?? null;

            $relations[] = $this->buildRelationMethod($relation['type'], $methodName, $relatedModel, $foreignKey);
        }

        return implode("\n\n", $relations);
    }

    protected function getRelationMethodName(array $relation): string
    {
        $model = $relation['model'];

        return match ($relation['type']) {
            'hasMany', 'belongsToMany', 'morphMany' => Str::camel(Str::pluralStudly($model)),
            default => Str::camel($model),
        };
    }

    protected function buildRelationMethod(string $type, string $methodName, string $model, ?string $foreignKey): string
    {
        $modelClass = "\\App\\Models\\{$model}";
        $foreignKeyParam = $foreignKey ? ", '{$foreignKey}'" : '';

        $method = match ($type) {
            'belongsTo' => "return \$this->belongsTo({$modelClass}::class{$foreignKeyParam});",
            'hasMany' => "return \$this->hasMany({$modelClass}::class{$foreignKeyParam});",
            'hasOne' => "return \$this->hasOne({$modelClass}::class{$foreignKeyParam});",
            'belongsToMany' => "return \$this->belongsToMany({$modelClass}::class);",
            'morphTo' => "return \$this->morphTo();",
            'morphMany' => "return \$this->morphMany({$modelClass}::class, '" . Str::snake($this->modelName) . "able');",
            default => "return \$this->belongsTo({$modelClass}::class{$foreignKeyParam});",
        };

        return <<<PHP
    /**
     * Get the {$methodName} relationship.
     */
    public function {$methodName}()
    {
        {$method}
    }
PHP;
    }

    protected function buildTraits(): string
    {
        $traits = [];

        if (config('crud-generator.soft_deletes')) {
            $traits[] = 'use SoftDeletes;';
        }

        return implode("\n    ", $traits);
    }

    protected function buildImports(): string
    {
        $imports = [
            'use Illuminate\Database\Eloquent\Factories\HasFactory;',
            'use Illuminate\Database\Eloquent\Model;',
        ];

        if (config('crud-generator.soft_deletes')) {
            $imports[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
        }

        // Voeg relatie imports toe
        foreach ($this->relations as $relation) {
            $relatedModel = $relation['model'];
            $import = "use App\\Models\\{$relatedModel};";
            if (!in_array($import, $imports)) {
                $imports[] = $import;
            }
        }

        return implode("\n", $imports);
    }
}
