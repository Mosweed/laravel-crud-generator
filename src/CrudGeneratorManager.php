<?php

namespace Mosweed\CrudGenerator;

use Illuminate\Contracts\Foundation\Application;
use Mosweed\CrudGenerator\Generators\ControllerGenerator;
use Mosweed\CrudGenerator\Generators\FactoryGenerator;
use Mosweed\CrudGenerator\Generators\MigrationGenerator;
use Mosweed\CrudGenerator\Generators\ModelGenerator;
use Mosweed\CrudGenerator\Generators\RequestGenerator;
use Mosweed\CrudGenerator\Generators\ResourceGenerator;
use Mosweed\CrudGenerator\Generators\RouteGenerator;
use Mosweed\CrudGenerator\Generators\SeederGenerator;
use Mosweed\CrudGenerator\Generators\ViewGenerator;

class CrudGeneratorManager
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Genereer alle CRUD componenten voor een model.
     */
    public function generate(array $config): array
    {
        $results = [];

        $generators = [
            'migration' => MigrationGenerator::class,
            'model' => ModelGenerator::class,
            'factory' => FactoryGenerator::class,
            'seeder' => SeederGenerator::class,
            'request' => RequestGenerator::class,
            'resource' => ResourceGenerator::class,
            'controller' => ControllerGenerator::class,
            'views' => ViewGenerator::class,
            'routes' => RouteGenerator::class,
        ];

        foreach ($generators as $name => $generatorClass) {
            if ($this->shouldGenerate($name, $config)) {
                try {
                    $generator = new $generatorClass($config);
                    $results[$name] = $generator->generate();
                } catch (\Exception $e) {
                    $results[$name] = false;
                    $results["{$name}_error"] = $e->getMessage();
                }
            }
        }

        return $results;
    }

    /**
     * Check of een bepaald component gegenereerd moet worden.
     */
    protected function shouldGenerate(string $type, array $config): bool
    {
        // Skip views voor API only
        if ($type === 'views' && ($config['api_only'] ?? false)) {
            return false;
        }

        return $config['generate'][$type] ?? config("crud-generator.generate.{$type}", true);
    }

    /**
     * Genereer alleen een specifiek component.
     */
    public function generateSingle(string $type, array $config): bool
    {
        $generators = [
            'migration' => MigrationGenerator::class,
            'model' => ModelGenerator::class,
            'factory' => FactoryGenerator::class,
            'seeder' => SeederGenerator::class,
            'request' => RequestGenerator::class,
            'resource' => ResourceGenerator::class,
            'controller' => ControllerGenerator::class,
            'views' => ViewGenerator::class,
            'routes' => RouteGenerator::class,
        ];

        if (!isset($generators[$type])) {
            throw new \InvalidArgumentException("Unknown generator type: {$type}");
        }

        $generator = new $generators[$type]($config);
        return $generator->generate();
    }
}
