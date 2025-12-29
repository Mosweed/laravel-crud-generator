<?php

namespace Mosweed\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;

class MakeCrudFromYamlCommand extends Command
{
    protected $signature = 'make:crud-yaml 
                            {file : Pad naar het YAML configuratiebestand}
                            {--force : Overschrijf bestaande bestanden}';

    protected $description = 'Genereer meerdere CRUDs vanuit een YAML configuratiebestand';

    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!File::exists($filePath)) {
            $this->error("Bestand niet gevonden: {$filePath}");
            return Command::FAILURE;
        }

        try {
            $config = Yaml::parseFile($filePath);
        } catch (\Exception $e) {
            $this->error("Kon YAML niet parsen: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("🚀 CRUD genereren vanuit YAML configuratie");
        $this->newLine();

        $models = $config['models'] ?? [];

        foreach ($models as $modelName => $modelConfig) {
            $this->info("📦 Genereren: {$modelName}");

            $fields = $this->formatFields($modelConfig['fields'] ?? []);
            $relations = $this->formatRelations($modelConfig['relations'] ?? []);

            $arguments = [
                'name' => $modelName,
                '--fields' => $fields,
                '--relations' => $relations,
            ];

            if ($this->option('force')) {
                $arguments['--force'] = true;
            }

            $this->call('make:crud', $arguments);
            $this->newLine();
        }

        $this->info("✅ Alle CRUDs succesvol gegenereerd!");

        return Command::SUCCESS;
    }

    protected function formatFields(array $fields): string
    {
        $formatted = [];
        foreach ($fields as $fieldName => $fieldConfig) {
            if (is_string($fieldConfig)) {
                // Simpele string of enum:value1:value2 syntax
                $formatted[] = "{$fieldName}:{$fieldConfig}";
            } else {
                $type = $fieldConfig['type'] ?? 'string';
                $modifiers = $fieldConfig['modifiers'] ?? [];
                
                // Filter out enum values from modifiers (voor backwards compatibility)
                $realModifiers = array_filter($modifiers, function($mod) {
                    // Echte modifiers zijn: nullable, unique, index, unsigned, default:X
                    return in_array($mod, ['nullable', 'unique', 'index', 'unsigned']) 
                        || str_starts_with($mod, 'default:');
                });
                
                $modifierStr = implode(':', $realModifiers);
                $formatted[] = $modifierStr ? "{$fieldName}:{$type}:{$modifierStr}" : "{$fieldName}:{$type}";
            }
        }
        return implode(',', $formatted);
    }

    protected function formatRelations(array $relations): string
    {
        $formatted = [];
        foreach ($relations as $relation) {
            $type = $relation['type'] ?? 'belongsTo';
            $model = $relation['model'] ?? '';
            $foreignKey = $relation['foreign_key'] ?? '';
            $formatted[] = $foreignKey ? "{$type}:{$model}:{$foreignKey}" : "{$type}:{$model}";
        }
        return implode(',', $formatted);
    }
}
