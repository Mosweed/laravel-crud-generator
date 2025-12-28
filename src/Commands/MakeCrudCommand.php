<?php

namespace Mosweed\CrudGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Mosweed\CrudGenerator\Generators\MigrationGenerator;
use Mosweed\CrudGenerator\Generators\ModelGenerator;
use Mosweed\CrudGenerator\Generators\ControllerGenerator;
use Mosweed\CrudGenerator\Generators\RequestGenerator;
use Mosweed\CrudGenerator\Generators\ResourceGenerator;
use Mosweed\CrudGenerator\Generators\SeederGenerator;
use Mosweed\CrudGenerator\Generators\FactoryGenerator;
use Mosweed\CrudGenerator\Generators\ViewGenerator;
use Mosweed\CrudGenerator\Generators\RouteGenerator;
use Mosweed\CrudGenerator\Support\FieldParser;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud 
                            {name : De naam van het model (bijv. Post of Blog/Post)}
                            {--fields= : Velden in formaat naam:type:modificaties (bijv. title:string:nullable,body:text)}
                            {--relations= : Relaties in formaat type:model:foreign_key (bijv. belongsTo:User:user_id)}
                            {--api : Genereer alleen API controller}
                            {--force : Overschrijf bestaande bestanden}
                            {--interactive : Interactieve modus met prompts}';

    protected $description = 'Genereer complete CRUD (Model, Migration, Controller, Views, Routes, Seeder, Factory)';

    protected array $fields = [];
    protected array $relations = [];
    protected string $modelName;
    protected string $modelNamespace;
    protected array $generateOptions = [];

    public function handle(): int
    {
        $this->modelName = $this->parseModelName($this->argument('name'));
        $this->modelNamespace = $this->parseModelNamespace($this->argument('name'));

        if ($this->option('interactive')) {
            $this->runInteractive();
        } else {
            $this->parseFieldsOption();
            $this->parseRelationsOption();
            $this->generateOptions = config('crud-generator.generate');
        }

        $this->info("🚀 CRUD genereren voor: {$this->modelName}");
        $this->newLine();

        $this->generateAll();

        $this->newLine();
        $this->info("✅ CRUD succesvol gegenereerd voor {$this->modelName}!");
        $this->showNextSteps();

        return Command::SUCCESS;
    }

    protected function runInteractive(): void
    {
        // Velden verzamelen
        $this->info('📝 Definieer de velden voor ' . $this->modelName);
        $this->newLine();

        $addMore = true;
        while ($addMore) {
            $fieldName = text(
                label: 'Veldnaam (leeg laten om te stoppen)',
                placeholder: 'bijv. title, email, price',
            );

            if (empty($fieldName)) {
                $addMore = false;
                continue;
            }

            $fieldType = select(
                label: "Type voor '{$fieldName}'",
                options: [
                    'string' => 'String (VARCHAR)',
                    'text' => 'Text (LONGTEXT)',
                    'integer' => 'Integer',
                    'bigInteger' => 'Big Integer',
                    'float' => 'Float',
                    'decimal' => 'Decimal',
                    'boolean' => 'Boolean',
                    'date' => 'Date',
                    'datetime' => 'DateTime',
                    'time' => 'Time',
                    'json' => 'JSON',
                    'enum' => 'Enum',
                ],
                default: 'string'
            );

            $modifiers = multiselect(
                label: "Modifiers voor '{$fieldName}'",
                options: [
                    'nullable' => 'Nullable',
                    'unique' => 'Unique',
                    'index' => 'Index',
                    'unsigned' => 'Unsigned',
                    'default' => 'Default value',
                ],
                default: []
            );

            $this->fields[] = [
                'name' => $fieldName,
                'type' => $fieldType,
                'modifiers' => $modifiers,
            ];

            $this->info("  ✓ Veld '{$fieldName}' toegevoegd");
        }

        // Relaties verzamelen
        $this->newLine();
        $hasRelations = confirm(
            label: 'Wil je relaties toevoegen?',
            default: false
        );

        if ($hasRelations) {
            $addMoreRelations = true;
            while ($addMoreRelations) {
                $relationType = select(
                    label: 'Type relatie',
                    options: [
                        'belongsTo' => 'Belongs To (N:1)',
                        'hasMany' => 'Has Many (1:N)',
                        'hasOne' => 'Has One (1:1)',
                        'belongsToMany' => 'Belongs To Many (N:N)',
                        'morphTo' => 'Morph To (Polymorphic)',
                        'morphMany' => 'Morph Many (Polymorphic)',
                    ]
                );

                $relatedModel = text(
                    label: 'Gerelateerd model',
                    placeholder: 'bijv. User, Category, Tag',
                    required: true
                );

                $foreignKey = text(
                    label: 'Foreign key (optioneel)',
                    placeholder: 'bijv. user_id',
                );

                $this->relations[] = [
                    'type' => $relationType,
                    'model' => $relatedModel,
                    'foreign_key' => $foreignKey ?: null,
                ];

                $this->info("  ✓ Relatie '{$relationType}' naar '{$relatedModel}' toegevoegd");

                $addMoreRelations = confirm(
                    label: 'Nog een relatie toevoegen?',
                    default: false
                );
            }
        }

        // Generatie opties
        $this->newLine();
        $this->generateOptions = [];
        
        $selectedOptions = multiselect(
            label: 'Wat wil je genereren?',
            options: [
                'model' => 'Model',
                'migration' => 'Migration',
                'controller' => 'Controller',
                'request' => 'Form Requests',
                'resource' => 'API Resource',
                'seeder' => 'Seeder',
                'factory' => 'Factory',
                'views' => 'Views (Blade)',
                'routes' => 'Routes',
            ],
            default: ['model', 'migration', 'controller', 'request', 'seeder', 'factory', 'views', 'routes']
        );

        foreach ($selectedOptions as $option) {
            $this->generateOptions[$option] = true;
        }
    }

    protected function parseFieldsOption(): void
    {
        $fieldsOption = $this->option('fields');
        if (empty($fieldsOption)) {
            return;
        }

        $this->fields = FieldParser::parse($fieldsOption);
    }

    protected function parseRelationsOption(): void
    {
        $relationsOption = $this->option('relations');
        if (empty($relationsOption)) {
            return;
        }

        $relations = explode(',', $relationsOption);
        foreach ($relations as $relation) {
            $parts = explode(':', $relation);
            $this->relations[] = [
                'type' => $parts[0] ?? 'belongsTo',
                'model' => $parts[1] ?? '',
                'foreign_key' => $parts[2] ?? null,
            ];
        }
    }

    protected function generateAll(): void
    {
        $config = [
            'model_name' => $this->modelName,
            'model_namespace' => $this->modelNamespace,
            'fields' => $this->fields,
            'relations' => $this->relations,
            'force' => $this->option('force'),
            'api_only' => $this->option('api'),
        ];

        // Migration eerst (voor foreign keys)
        if ($this->shouldGenerate('migration')) {
            $this->task('Migration', fn() => (new MigrationGenerator($config))->generate());
        }

        // Model
        if ($this->shouldGenerate('model')) {
            $this->task('Model', fn() => (new ModelGenerator($config))->generate());
        }

        // Factory
        if ($this->shouldGenerate('factory')) {
            $this->task('Factory', fn() => (new FactoryGenerator($config))->generate());
        }

        // Seeder
        if ($this->shouldGenerate('seeder')) {
            $this->task('Seeder', fn() => (new SeederGenerator($config))->generate());
        }

        // Request
        if ($this->shouldGenerate('request')) {
            $this->task('Form Requests', fn() => (new RequestGenerator($config))->generate());
        }

        // Resource
        if ($this->shouldGenerate('resource')) {
            $this->task('API Resource', fn() => (new ResourceGenerator($config))->generate());
        }

        // Controller
        if ($this->shouldGenerate('controller')) {
            $this->task('Controller', fn() => (new ControllerGenerator($config))->generate());
        }

        // Views
        if ($this->shouldGenerate('views') && !$this->option('api')) {
            $this->task('Views', fn() => (new ViewGenerator($config))->generate());
        }

        // Routes
        if ($this->shouldGenerate('routes')) {
            $this->task('Routes', fn() => (new RouteGenerator($config))->generate());
        }
    }

    protected function shouldGenerate(string $type): bool
    {
        return $this->generateOptions[$type] ?? config("crud-generator.generate.{$type}", true);
    }

    protected function task(string $name, callable $callback): void
    {
        $this->output->write("  <comment>Genereren:</comment> {$name}... ");
        
        try {
            $result = $callback();
            $this->output->writeln('<info>✓</info>');
        } catch (\Exception $e) {
            $this->output->writeln('<error>✗</error>');
            $this->error("    Error: " . $e->getMessage());
        }
    }

    protected function parseModelName(string $name): string
    {
        $parts = explode('/', $name);
        return Str::studly(end($parts));
    }

    protected function parseModelNamespace(string $name): string
    {
        $parts = explode('/', $name);
        array_pop($parts);
        
        if (empty($parts)) {
            return config('crud-generator.namespace') . '\\Models';
        }

        return config('crud-generator.namespace') . '\\Models\\' . implode('\\', array_map([Str::class, 'studly'], $parts));
    }

    protected function showNextSteps(): void
    {
        $tableName = Str::snake(Str::pluralStudly($this->modelName));
        
        $this->newLine();
        $this->info('📋 Volgende stappen:');
        $this->line("  1. Controleer de migration in database/migrations/");
        $this->line("  2. Pas het model aan in app/Models/{$this->modelName}.php");
        $this->line("  3. Voer uit: <comment>php artisan migrate</comment>");
        $this->line("  4. Voer uit: <comment>php artisan db:seed --class={$this->modelName}Seeder</comment>");
        $this->newLine();
        
        if (!$this->option('api')) {
            $routeName = Str::kebab(Str::pluralStudly($this->modelName));
            $this->line("  Routes beschikbaar op: <comment>/{$routeName}</comment>");
        }
    }
}
