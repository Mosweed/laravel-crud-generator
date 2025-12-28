<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Support\Str;

class ControllerGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $isApi = $this->config['api_only'] ?? config('crud-generator.api_only');
        
        $stub = $isApi ? $this->getStub('controller.api') : $this->getStub('controller');

        $content = $this->replaceStubVariables($stub, [
            'namespace' => config('crud-generator.namespace') . '\\Http\\Controllers',
            'class_name' => $this->getControllerName(),
            'model_name' => $this->modelName,
            'model_namespace' => $this->modelNamespace,
            'model_variable' => $this->getModelVariable(),
            'model_variable_plural' => $this->getModelVariablePlural(),
            'view_folder' => $this->getViewFolder(),
            'route_prefix' => $this->getRoutePrefix(),
            'store_request' => "Store{$this->modelName}Request",
            'update_request' => "Update{$this->modelName}Request",
            'resource_name' => "{$this->modelName}Resource",
            'with_relations' => $this->buildWithRelations(),
            'search_fields' => $this->buildSearchFields(),
        ]);

        $path = config('crud-generator.paths.controller') . '/' . $this->getControllerName() . '.php';

        return $this->putFile($path, $content);
    }

    protected function buildWithRelations(): string
    {
        if (empty($this->relations)) {
            return '';
        }

        $relations = array_map(function ($relation) {
            $methodName = match ($relation['type']) {
                'hasMany', 'belongsToMany', 'morphMany' => Str::camel(Str::pluralStudly($relation['model'])),
                default => Str::camel($relation['model']),
            };
            return "'{$methodName}'";
        }, $this->relations);

        return '->with([' . implode(', ', $relations) . '])';
    }

    protected function buildSearchFields(): string
    {
        $searchable = [];

        foreach ($this->fields as $field) {
            if (in_array($field['type'], ['string', 'text', 'enum'])) {
                $searchable[] = "'{$field['name']}'";
            }
        }

        if (empty($searchable)) {
            return "['id']";
        }

        return '[' . implode(', ', $searchable) . ']';
    }
}
