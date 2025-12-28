<?php

namespace Mosweed\CrudGenerator;

use Illuminate\Support\ServiceProvider;
use Mosweed\CrudGenerator\Commands\MakeCrudCommand;
use Mosweed\CrudGenerator\Commands\MakeCrudFromYamlCommand;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/crud-generator.php', 'crud-generator');

        $this->app->singleton('crud-generator', function ($app) {
            return new CrudGeneratorManager($app);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCrudCommand::class,
                MakeCrudFromYamlCommand::class,
            ]);

            // Config publiceren
            $this->publishes([
                __DIR__ . '/../config/crud-generator.php' => config_path('crud-generator.php'),
            ], 'crud-generator-config');

            // Stubs publiceren
            $this->publishes([
                __DIR__ . '/Stubs' => resource_path('stubs/vendor/crud-generator'),
            ], 'crud-generator-stubs');

            // CSS/Tailwind bestanden publiceren
            $this->publishes([
                __DIR__ . '/../resources/css/crud-theme.css' => resource_path('css/vendor/crud-generator/crud-theme.css'),
                __DIR__ . '/../resources/css/crud-components.css' => resource_path('css/vendor/crud-generator/crud-components.css'),
                __DIR__ . '/../tailwind.config.js' => base_path('tailwind.crud.config.js'),
            ], 'crud-generator-assets');

            // Alles in één keer publiceren
            $this->publishes([
                __DIR__ . '/../config/crud-generator.php' => config_path('crud-generator.php'),
                __DIR__ . '/../resources/css/crud-theme.css' => resource_path('css/vendor/crud-generator/crud-theme.css'),
                __DIR__ . '/../resources/css/crud-components.css' => resource_path('css/vendor/crud-generator/crud-components.css'),
            ], 'crud-generator');
        }
    }
}
