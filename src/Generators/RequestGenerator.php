<?php

namespace Mosweed\CrudGenerator\Generators;

use Mosweed\CrudGenerator\Support\FieldParser;

class RequestGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $this->generateStoreRequest();
        $this->generateUpdateRequest();

        return true;
    }

    protected function generateStoreRequest(): bool
    {
        $stub = $this->getStub('request');

        $content = $this->replaceStubVariables($stub, [
            'namespace' => config('crud-generator.namespace') . '\\Http\\Requests',
            'class_name' => "Store{$this->modelName}Request",
            'rules' => $this->buildRules(false),
            'messages' => $this->buildMessages(),
        ]);

        $path = config('crud-generator.paths.request') . "/Store{$this->modelName}Request.php";

        return $this->putFile($path, $content);
    }

    protected function generateUpdateRequest(): bool
    {
        $stub = $this->getStub('request');

        $content = $this->replaceStubVariables($stub, [
            'namespace' => config('crud-generator.namespace') . '\\Http\\Requests',
            'class_name' => "Update{$this->modelName}Request",
            'rules' => $this->buildRules(true),
            'messages' => $this->buildMessages(),
        ]);

        $path = config('crud-generator.paths.request') . "/Update{$this->modelName}Request.php";

        return $this->putFile($path, $content);
    }

    protected function buildRules(bool $isUpdate): string
    {
        $rules = [];

        foreach ($this->fields as $field) {
            $rule = FieldParser::toValidationRules($field, $isUpdate);
            $rules[] = "'{$field['name']}' => '{$rule}'";
        }

        return implode(",\n            ", $rules);
    }

    protected function buildMessages(): string
    {
        $messages = [];

        foreach ($this->fields as $field) {
            $label = ucfirst(str_replace('_', ' ', $field['name']));
            
            if (!in_array('nullable', $field['modifiers'] ?? [])) {
                $messages[] = "'{$field['name']}.required' => '{$label} is verplicht.'";
            }

            // Type-specifieke berichten
            $typeMessage = match ($field['type']) {
                'integer', 'bigInteger' => "'{$field['name']}.integer' => '{$label} moet een geheel getal zijn.'",
                'float', 'decimal' => "'{$field['name']}.numeric' => '{$label} moet een nummer zijn.'",
                'boolean' => "'{$field['name']}.boolean' => '{$label} moet ja of nee zijn.'",
                'date', 'datetime' => "'{$field['name']}.date' => '{$label} moet een geldige datum zijn.'",
                'json' => "'{$field['name']}.json' => '{$label} moet geldige JSON zijn.'",
                default => null,
            };

            if ($typeMessage) {
                $messages[] = $typeMessage;
            }
        }

        return implode(",\n            ", $messages);
    }
}
