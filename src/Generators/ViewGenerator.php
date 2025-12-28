<?php

namespace Mosweed\CrudGenerator\Generators;

use Illuminate\Support\Str;
use Mosweed\CrudGenerator\Support\FieldParser;

class ViewGenerator extends BaseGenerator
{
    public function generate(): bool
    {
        $viewFolder = $this->getViewFolder();
        $basePath = config('crud-generator.paths.view') . '/' . $viewFolder;

        $this->generateIndexView($basePath);
        $this->generateCreateView($basePath);
        $this->generateEditView($basePath);
        $this->generateShowView($basePath);
        $this->generateFormPartial($basePath);

        return true;
    }

    protected function generateIndexView(string $basePath): void
    {
        $stub = $this->getStub('views/index');
        
        $content = $this->replaceStubVariables($stub, [
            'model_name' => $this->modelName,
            'model_name_plural' => Str::pluralStudly($this->modelName),
            'model_variable' => $this->getModelVariable(),
            'model_variable_plural' => $this->getModelVariablePlural(),
            'route_prefix' => $this->getRoutePrefix(),
            'table_headers' => $this->buildTableHeaders(),
            'table_body' => $this->buildTableBody(),
        ]);

        $this->putFile("{$basePath}/index.blade.php", $content);
    }

    protected function generateCreateView(string $basePath): void
    {
        $stub = $this->getStub('views/create');
        
        $content = $this->replaceStubVariables($stub, [
            'model_name' => $this->modelName,
            'model_variable' => $this->getModelVariable(),
            'route_prefix' => $this->getRoutePrefix(),
        ]);

        $this->putFile("{$basePath}/create.blade.php", $content);
    }

    protected function generateEditView(string $basePath): void
    {
        $stub = $this->getStub('views/edit');
        
        $content = $this->replaceStubVariables($stub, [
            'model_name' => $this->modelName,
            'model_variable' => $this->getModelVariable(),
            'route_prefix' => $this->getRoutePrefix(),
        ]);

        $this->putFile("{$basePath}/edit.blade.php", $content);
    }

    protected function generateShowView(string $basePath): void
    {
        $stub = $this->getStub('views/show');
        
        $content = $this->replaceStubVariables($stub, [
            'model_name' => $this->modelName,
            'model_variable' => $this->getModelVariable(),
            'route_prefix' => $this->getRoutePrefix(),
            'detail_fields' => $this->buildDetailFields(),
        ]);

        $this->putFile("{$basePath}/show.blade.php", $content);
    }

    protected function generateFormPartial(string $basePath): void
    {
        $stub = $this->getStub('views/form');
        
        $content = $this->replaceStubVariables($stub, [
            'model_variable' => $this->getModelVariable(),
            'form_fields' => $this->buildFormFields(),
        ]);

        $this->putFile("{$basePath}/_form.blade.php", $content);
    }

    protected function buildTableHeaders(): string
    {
        $headers = ['<th class="crud-table-th">ID</th>'];

        foreach ($this->fields as $field) {
            $label = ucfirst(str_replace('_', ' ', $field['name']));
            $headers[] = "<th class=\"crud-table-th\">{$label}</th>";
        }

        $headers[] = '<th class="crud-table-th text-right">Acties</th>';

        return implode("\n                    ", $headers);
    }

    protected function buildTableBody(): string
    {
        $variable = $this->getModelVariable();
        $cells = ["<td class=\"crud-table-td\">{{ \${$variable}->id }}</td>"];

        foreach ($this->fields as $field) {
            $name = $field['name'];
            $cell = match ($field['type']) {
                'boolean' => "<td class=\"crud-table-td\">
                                @if(\${$variable}->{$name})
                                    <span class=\"crud-badge-success\">Ja</span>
                                @else
                                    <span class=\"crud-badge-danger\">Nee</span>
                                @endif
                            </td>",
                'date' => "<td class=\"crud-table-td\">{{ \${$variable}->{$name}?->format('d-m-Y') }}</td>",
                'datetime', 'timestamp' => "<td class=\"crud-table-td\">{{ \${$variable}->{$name}?->format('d-m-Y H:i') }}</td>",
                'text' => "<td class=\"crud-table-td\">{{ Str::limit(\${$variable}->{$name}, 50) }}</td>",
                'json' => "<td class=\"crud-table-td\"><code class=\"text-xs\">{{ Str::limit(json_encode(\${$variable}->{$name}), 30) }}</code></td>",
                'enum' => "<td class=\"crud-table-td\"><span class=\"crud-badge-primary\">{{ ucfirst(\${$variable}->{$name}) }}</span></td>",
                default => "<td class=\"crud-table-td\">{{ \${$variable}->{$name} }}</td>",
            };
            $cells[] = $cell;
        }

        return implode("\n                    ", $cells);
    }

    protected function buildDetailFields(): string
    {
        $variable = $this->getModelVariable();
        $fields = [];

        foreach ($this->fields as $field) {
            $name = $field['name'];
            $label = ucfirst(str_replace('_', ' ', $name));
            
            $value = match ($field['type']) {
                'boolean' => "@if(\${$variable}->{$name})<span class=\"crud-badge-success\">Ja</span>@else<span class=\"crud-badge-danger\">Nee</span>@endif",
                'date' => "{{ \${$variable}->{$name}?->format('d-m-Y') }}",
                'datetime', 'timestamp' => "{{ \${$variable}->{$name}?->format('d-m-Y H:i:s') }}",
                'text' => "<div class=\"prose prose-sm max-w-none\">{!! nl2br(e(\${$variable}->{$name})) !!}</div>",
                'json' => "<pre class=\"bg-gray-50 p-2 rounded text-xs overflow-auto\">{{ json_encode(\${$variable}->{$name}, JSON_PRETTY_PRINT) }}</pre>",
                'enum' => "<span class=\"crud-badge-primary\">{{ ucfirst(\${$variable}->{$name}) }}</span>",
                default => "{{ \${$variable}->{$name} }}",
            };

            $fields[] = <<<HTML
<div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{$label}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{$value}</dd>
            </div>
HTML;
        }

        return implode("\n            ", $fields);
    }

    protected function buildFormFields(): string
    {
        $variable = $this->getModelVariable();
        $fields = [];

        foreach ($this->fields as $field) {
            $name = $field['name'];
            $label = ucfirst(str_replace('_', ' ', $name));
            $type = FieldParser::toInputType($field);
            $required = !in_array('nullable', $field['modifiers'] ?? []) ? 'required' : '';

            $input = match ($type) {
                'textarea' => $this->buildTextarea($name, $label, $variable, $required),
                'checkbox' => $this->buildCheckbox($name, $label, $variable),
                'select' => $this->buildSelect($name, $label, $variable, $field['enum_values'] ?? [], $required),
                default => $this->buildInput($name, $label, $type, $variable, $required),
            };

            $fields[] = $input;
        }

        return implode("\n\n        ", $fields);
    }

    protected function buildInput(string $name, string $label, string $type, string $variable, string $required): string
    {
        $step = in_array($type, ['number']) ? ' step="any"' : '';
        
        return <<<HTML
<div>
            <label for="{$name}" class="crud-label">{$label}</label>
            <input type="{$type}" name="{$name}" id="{$name}" 
                   value="{{ old('{$name}', \${$variable}?->{$name}) }}"
                   class="crud-input mt-1 @error('{$name}') crud-input-error @enderror"
                   {$required}{$step}>
            @error('{$name}')
                <p class="crud-label-error">{{ \$message }}</p>
            @enderror
        </div>
HTML;
    }

    protected function buildTextarea(string $name, string $label, string $variable, string $required): string
    {
        return <<<HTML
<div>
            <label for="{$name}" class="crud-label">{$label}</label>
            <textarea name="{$name}" id="{$name}" rows="4"
                      class="crud-textarea mt-1 @error('{$name}') crud-input-error @enderror"
                      {$required}>{{ old('{$name}', \${$variable}?->{$name}) }}</textarea>
            @error('{$name}')
                <p class="crud-label-error">{{ \$message }}</p>
            @enderror
        </div>
HTML;
    }

    protected function buildCheckbox(string $name, string $label, string $variable): string
    {
        return <<<HTML
<div class="flex items-start">
            <div class="flex items-center h-5">
                <input type="hidden" name="{$name}" value="0">
                <input type="checkbox" name="{$name}" id="{$name}" value="1"
                       {{ old('{$name}', \${$variable}?->{$name}) ? 'checked' : '' }}
                       class="crud-checkbox">
            </div>
            <div class="ml-3 text-sm">
                <label for="{$name}" class="font-medium text-gray-700">{$label}</label>
            </div>
        </div>
HTML;
    }

    protected function buildSelect(string $name, string $label, string $variable, array $options, string $required): string
    {
        $optionsHtml = '<option value="">Selecteer...</option>';
        foreach ($options as $option) {
            $optionLabel = ucfirst($option);
            $optionsHtml .= "\n                <option value=\"{$option}\" {{ old('{$name}', \${$variable}?->{$name}) === '{$option}' ? 'selected' : '' }}>{$optionLabel}</option>";
        }

        return <<<HTML
<div>
            <label for="{$name}" class="crud-label">{$label}</label>
            <select name="{$name}" id="{$name}"
                    class="crud-select mt-1 @error('{$name}') crud-input-error @enderror"
                    {$required}>
                {$optionsHtml}
            </select>
            @error('{$name}')
                <p class="crud-label-error">{{ \$message }}</p>
            @enderror
        </div>
HTML;
    }
}
