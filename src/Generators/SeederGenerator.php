<?php

namespace Mosweed\CrudGenerator\Generators;

class SeederGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $stub = $this->getStub('seeder');

        $content = $this->replaceStubVariables($stub, [
            'class_name' => "{$this->modelName}Seeder",
            'model_name' => $this->modelName,
            'model_namespace' => $this->modelNamespace,
            'factory_count' => config('crud-generator.pagination.per_page', 15) * 2,
        ]);

        $path = config('crud-generator.paths.seeder') . "/{$this->modelName}Seeder.php";

        return $this->putFile($path, $content);
    }
}
