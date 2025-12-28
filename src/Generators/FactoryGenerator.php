<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Support\Str;
use Mosweed\CrudGenerator\Support\FieldParser;

class FactoryGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $stub = $this->getStub('factory');

        $content = $this->replaceStubVariables($stub, [
            'namespace' => 'Database\\Factories',
            'class_name' => "{$this->modelName}Factory",
            'model_name' => $this->modelName,
            'model_namespace' => $this->modelNamespace,
            'definitions' => $this->buildDefinitions(),
            'states' => $this->buildStates(),
        ]);

        $path = config('crud-generator.paths.factory') . "/{$this->modelName}Factory.php";

        return $this->putFile($path, $content);
    }

    protected function buildDefinitions(): string
    {
        $definitions = [];

        foreach ($this->fields as $field) {
            $faker = FieldParser::toFakerDefinition($field);
            $definitions[] = "'{$field['name']}' => {$faker}";
        }

        // Voeg foreign keys toe voor belongsTo relaties
        foreach ($this->relations as $relation) {
            if ($relation['type'] === 'belongsTo') {
                $foreignKey = $relation['foreign_key'] ?? Str::snake($relation['model']) . '_id';
                $relatedModel = $relation['model'];
                
                // Check of de foreign key niet al in de definitie zit
                $exists = collect($definitions)->contains(fn($d) => str_contains($d, "'{$foreignKey}'"));
                
                if (!$exists) {
                    $definitions[] = "'{$foreignKey}' => \\App\\Models\\{$relatedModel}::factory()";
                }
            }
        }

        return implode(",\n            ", $definitions);
    }

    protected function buildStates(): string
    {
        $states = [];

        // Genereer states voor enum velden
        foreach ($this->fields as $field) {
            if ($field['type'] === 'enum' && !empty($field['enum_values'])) {
                foreach ($field['enum_values'] as $value) {
                    $stateName = Str::camel($value);
                    $states[] = $this->buildStateMethod($stateName, $field['name'], $value);
                }
            }
        }

        // Boolean states
        foreach ($this->fields as $field) {
            if ($field['type'] === 'boolean') {
                $enabledName = Str::camel($field['name']);
                $disabledName = Str::camel('not_' . $field['name']);
                
                $states[] = $this->buildStateMethod($enabledName, $field['name'], 'true', false);
                $states[] = $this->buildStateMethod($disabledName, $field['name'], 'false', false);
            }
        }

        return implode("\n\n", $states);
    }

    protected function buildStateMethod(string $name, string $field, string $value, bool $quoted = true): string
    {
        $valueStr = $quoted ? "'{$value}'" : $value;
        
        return <<<PHP
    /**
     * Indicate that the model is {$name}.
     */
    public function {$name}(): static
    {
        return \$this->state(fn (array \$attributes) => [
            '{$field}' => {$valueStr},
        ]);
    }
PHP;
    }
}
