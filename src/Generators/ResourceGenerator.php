<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Support\Str;

class ResourceGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $stub = $this->getStub('resource');

        $content = $this->replaceStubVariables($stub, [
            'namespace' => config('crud-generator.namespace') . '\\Http\\Resources',
            'class_name' => "{$this->modelName}Resource",
            'fields' => $this->buildResourceFields(),
            'relations' => $this->buildResourceRelations(),
        ]);

        $path = config('crud-generator.paths.resource') . "/{$this->modelName}Resource.php";

        return $this->putFile($path, $content);
    }

    protected function buildResourceFields(): string
    {
        $fields = [
            "'id' => \$this->id",
        ];

        foreach ($this->fields as $field) {
            $name = $field['name'];
            $fields[] = "'{$name}' => \$this->{$name}";
        }

        // Timestamps
        $fields[] = "'created_at' => \$this->created_at?->toISOString()";
        $fields[] = "'updated_at' => \$this->updated_at?->toISOString()";

        return implode(",\n            ", $fields);
    }

    protected function buildResourceRelations(): string
    {
        if (empty($this->relations)) {
            return '';
        }

        $relations = [];

        foreach ($this->relations as $relation) {
            $methodName = match ($relation['type']) {
                'hasMany', 'belongsToMany', 'morphMany' => Str::camel(Str::pluralStudly($relation['model'])),
                default => Str::camel($relation['model']),
            };

            $resourceClass = "{$relation['model']}Resource";

            if (in_array($relation['type'], ['hasMany', 'belongsToMany', 'morphMany'])) {
                $relations[] = "'{$methodName}' => {$resourceClass}::collection(\$this->whenLoaded('{$methodName}'))";
            } else {
                $relations[] = "'{$methodName}' => new {$resourceClass}(\$this->whenLoaded('{$methodName}'))";
            }
        }

        if (empty($relations)) {
            return '';
        }

        return ",\n            " . implode(",\n            ", $relations);
    }
}
