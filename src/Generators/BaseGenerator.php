<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

abstract class BaseGenerator
{
    protected Filesystem $files;
    protected array $config;
    protected string $modelName;
    protected string $modelNamespace;
    protected array $fields;
    protected array $relations;

    public function __construct(array $config)
    {
        $this->files = new Filesystem();
        $this->config = $config;
        $this->modelName = $config['model_name'];
        $this->modelNamespace = $config['model_namespace'];
        $this->fields = $config['fields'] ?? [];
        $this->relations = $config['relations'] ?? [];
    }

    abstract public function generate(): bool;

    protected function getStub(string $name): string
    {
        $customPath = resource_path("stubs/vendor/crud-generator/{$name}.stub");
        
        if ($this->files->exists($customPath)) {
            return $this->files->get($customPath);
        }

        return $this->files->get(__DIR__ . "/../Stubs/{$name}.stub");
    }

    protected function replaceStubVariables(string $stub, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $stub = str_replace("{{ {$key} }}", $value, $stub);
            $stub = str_replace("{{{$key}}}", $value, $stub);
        }

        return $stub;
    }

    protected function makeDirectory(string $path): void
    {
        $directory = dirname($path);
        
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    protected function putFile(string $path, string $content): bool
    {
        $this->makeDirectory($path);

        if ($this->files->exists($path) && !($this->config['force'] ?? false)) {
            return false;
        }

        return (bool) $this->files->put($path, $content);
    }

    // Helper methods voor namen
    protected function getModelVariable(): string
    {
        return Str::camel($this->modelName);
    }

    protected function getModelVariablePlural(): string
    {
        return Str::camel(Str::pluralStudly($this->modelName));
    }

    protected function getTableName(): string
    {
        return Str::snake(Str::pluralStudly($this->modelName));
    }

    protected function getRoutePrefix(): string
    {
        return Str::kebab(Str::pluralStudly($this->modelName));
    }

    protected function getViewFolder(): string
    {
        return Str::kebab(Str::pluralStudly($this->modelName));
    }

    protected function getControllerName(): string
    {
        return $this->modelName . 'Controller';
    }

    protected function getFullModelClass(): string
    {
        return $this->modelNamespace . '\\' . $this->modelName;
    }
}
