<?php

namespace Mosweed\CrudGenerator\Tests\Unit;

use Mosweed\CrudGenerator\Generators\MigrationGenerator;
use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class MigrationGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_migration_file(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
                ['name' => 'body', 'type' => 'text', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new MigrationGenerator($config);
        $result = $generator->generate();

        $this->assertTrue($result);

        // Check of migration bestand bestaat
        $files = File::glob($this->testOutputPath . '/migrations/*_create_posts_table.php');
        $this->assertCount(1, $files);
    }

    /** @test */
    public function it_generates_migration_with_correct_table_name(): void
    {
        $config = [
            'model_name' => 'BlogPost',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_blog_posts_table.php');
        $this->assertCount(1, $files);

        $content = File::get($files[0]);
        $this->assertStringContainsString("Schema::create('blog_posts'", $content);
    }

    /** @test */
    public function it_generates_migration_with_columns(): void
    {
        $config = [
            'model_name' => 'Product',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'name', 'type' => 'string', 'modifiers' => []],
                ['name' => 'price', 'type' => 'decimal', 'modifiers' => []],
                ['name' => 'description', 'type' => 'text', 'modifiers' => ['nullable']],
                ['name' => 'is_active', 'type' => 'boolean', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_products_table.php');
        $content = File::get($files[0]);

        $this->assertStringContainsString("\$table->string('name')", $content);
        $this->assertStringContainsString("\$table->decimal('price'", $content);
        $this->assertStringContainsString("\$table->text('description')->nullable()", $content);
        $this->assertStringContainsString("\$table->boolean('is_active')", $content);
    }

    /** @test */
    public function it_generates_migration_with_foreign_key_for_belongs_to(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [
                ['type' => 'belongsTo', 'model' => 'User', 'foreign_key' => null],
            ],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_posts_table.php');
        $content = File::get($files[0]);

        $this->assertStringContainsString("foreignId('user_id')", $content);
        $this->assertStringContainsString("->constrained()", $content);
    }

    /** @test */
    public function it_generates_migration_with_custom_foreign_key(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [
                ['type' => 'belongsTo', 'model' => 'User', 'foreign_key' => 'author_id'],
            ],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_posts_table.php');
        $content = File::get($files[0]);

        $this->assertStringContainsString("foreignId('author_id')", $content);
    }

    /** @test */
    public function it_generates_migration_with_timestamps(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_posts_table.php');
        $content = File::get($files[0]);

        $this->assertStringContainsString('$table->timestamps()', $content);
    }

    /** @test */
    public function it_generates_migration_with_soft_deletes(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_posts_table.php');
        $content = File::get($files[0]);

        $this->assertStringContainsString('$table->softDeletes()', $content);
    }

    /** @test */
    public function it_generates_migration_with_enum_column(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'status', 'type' => 'enum', 'modifiers' => [], 'enum_values' => ['draft', 'published']],
            ],
            'relations' => [],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_posts_table.php');
        $content = File::get($files[0]);

        $this->assertStringContainsString("\$table->enum('status', ['draft', 'published'])", $content);
    }

    /** @test */
    public function it_generates_migration_with_unique_constraint(): void
    {
        $config = [
            'model_name' => 'User',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'email', 'type' => 'string', 'modifiers' => ['unique']],
            ],
            'relations' => [],
        ];

        $generator = new MigrationGenerator($config);
        $generator->generate();

        $files = File::glob($this->testOutputPath . '/migrations/*_create_users_table.php');
        $content = File::get($files[0]);

        $this->assertStringContainsString("->unique()", $content);
    }
}
