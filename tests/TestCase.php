<?php

namespace Mosweed\CrudGenerator\Tests;

use Mosweed\CrudGenerator\CrudGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Support\Facades\File;

abstract class TestCase extends Orchestra
{
    protected string $testOutputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testOutputPath = sys_get_temp_dir() . '/crud-generator-tests';
        
        // Maak test directory aan
        if (!File::isDirectory($this->testOutputPath)) {
            File::makeDirectory($this->testOutputPath, 0755, true);
        }

        // Override config paths voor tests
        config([
            'crud-generator.paths.model' => $this->testOutputPath . '/Models',
            'crud-generator.paths.controller' => $this->testOutputPath . '/Controllers',
            'crud-generator.paths.request' => $this->testOutputPath . '/Requests',
            'crud-generator.paths.resource' => $this->testOutputPath . '/Resources',
            'crud-generator.paths.migration' => $this->testOutputPath . '/migrations',
            'crud-generator.paths.seeder' => $this->testOutputPath . '/seeders',
            'crud-generator.paths.factory' => $this->testOutputPath . '/factories',
            'crud-generator.paths.view' => $this->testOutputPath . '/views',
            'crud-generator.paths.route' => $this->testOutputPath . '/routes',
        ]);

        // Maak routes bestanden aan
        File::makeDirectory($this->testOutputPath . '/routes', 0755, true, true);
        File::put($this->testOutputPath . '/routes/web.php', "<?php\n\nuse Illuminate\Support\Facades\Route;\n");
        File::put($this->testOutputPath . '/routes/api.php', "<?php\n\nuse Illuminate\Support\Facades\Route;\n");
    }

    protected function tearDown(): void
    {
        // Ruim test bestanden op
        if (File::isDirectory($this->testOutputPath)) {
            File::deleteDirectory($this->testOutputPath);
        }

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [
            CrudGeneratorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Helper om te controleren of een bestand bepaalde inhoud bevat.
     */
    protected function assertFileContainsString(string $needle, string $filePath): void
    {
        $this->assertFileExists($filePath);
        $content = File::get($filePath);
        $this->assertStringContainsString($needle, $content, "File {$filePath} does not contain expected string.");
    }

    /**
     * Helper om te controleren of een bestand NIET bepaalde inhoud bevat.
     */
    protected function assertFileNotContainsString(string $needle, string $filePath): void
    {
        $this->assertFileExists($filePath);
        $content = File::get($filePath);
        $this->assertStringNotContainsString($needle, $content, "File {$filePath} unexpectedly contains string.");
    }

    /**
     * Helper om gegenereerd bestand te lezen.
     */
    protected function getGeneratedFileContent(string $relativePath): string
    {
        $fullPath = $this->testOutputPath . '/' . $relativePath;
        $this->assertFileExists($fullPath);
        return File::get($fullPath);
    }
}
