<?php

namespace Mosweed\CrudGenerator\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class MakeCrudFromYamlCommandTest extends TestCase
{
    protected string $yamlPath;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->yamlPath = $this->testOutputPath . '/crud-config.yaml';
    }

    #[Test]
    public function it_can_run_yaml_command(): void
    {
        $yaml = <<<YAML
models:
  Post:
    fields:
      title: string
      body: text
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ])
        ->assertExitCode(0);
    }

    #[Test]
    public function it_generates_multiple_models_from_yaml(): void
    {
        $yaml = <<<YAML
models:
  Category:
    fields:
      name: string
      slug: string
  Product:
    fields:
      name: string
      price: decimal
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ]);

        $this->assertFileExists($this->testOutputPath . '/Models/Category.php');
        $this->assertFileExists($this->testOutputPath . '/Models/Product.php');
    }

    #[Test]
    public function it_handles_yaml_with_relations(): void
    {
        $yaml = <<<YAML
models:
  Comment:
    fields:
      body: text
    relations:
      - type: belongsTo
        model: Post
      - type: belongsTo
        model: User
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ]);

        $modelContent = File::get($this->testOutputPath . '/Models/Comment.php');
        
        $this->assertStringContainsString('public function post()', $modelContent);
        $this->assertStringContainsString('public function user()', $modelContent);
    }

    #[Test]
    public function it_handles_yaml_with_field_modifiers(): void
    {
        $yaml = <<<YAML
models:
  User:
    fields:
      name: string
      email:
        type: string
        modifiers: [unique]
      bio:
        type: text
        modifiers: [nullable]
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ]);

        $migrations = File::glob($this->testOutputPath . '/migrations/*_create_users_table.php');
        $migrationContent = File::get($migrations[0]);
        
        $this->assertStringContainsString('->unique()', $migrationContent);
        $this->assertStringContainsString('->nullable()', $migrationContent);
    }

    #[Test]
    public function it_handles_yaml_with_enum_fields(): void
    {
        $yaml = <<<YAML
models:
  Order:
    fields:
      status:
        type: enum
        modifiers: [pending, processing, shipped, delivered]
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ]);

        $migrations = File::glob($this->testOutputPath . '/migrations/*_create_orders_table.php');
        $migrationContent = File::get($migrations[0]);
        
        $this->assertStringContainsString('enum', $migrationContent);
    }

    #[Test]
    public function it_fails_gracefully_for_missing_file(): void
    {
        $this->artisan('make:crud-yaml', [
            'file' => '/nonexistent/path/config.yaml',
        ])
        ->assertExitCode(1);
    }

    #[Test]
    public function it_fails_gracefully_for_invalid_yaml(): void
    {
        $invalidYaml = <<<YAML
models:
  Post:
    fields:
      - this: is
        not: valid: yaml: syntax
YAML;

        File::put($this->yamlPath, $invalidYaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ])
        ->assertExitCode(1);
    }

    #[Test]
    public function it_handles_yaml_with_custom_foreign_keys(): void
    {
        $yaml = <<<YAML
models:
  Post:
    fields:
      title: string
    relations:
      - type: belongsTo
        model: User
        foreign_key: author_id
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ]);

        $modelContent = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString("'author_id'", $modelContent);
    }

    #[Test]
    public function it_handles_force_option(): void
    {
        $yaml = <<<YAML
models:
  Article:
    fields:
      title: string
YAML;

        File::put($this->yamlPath, $yaml);

        // First run
        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ]);

        // Second run with force
        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
            '--force' => true,
        ])
        ->assertExitCode(0);
    }

    #[Test]
    public function it_generates_complete_crud_for_complex_yaml(): void
    {
        $yaml = <<<YAML
models:
  Category:
    fields:
      name: string
      slug:
        type: string
        modifiers: [unique]
      description:
        type: text
        modifiers: [nullable]
    relations:
      - type: hasMany
        model: Post

  Post:
    fields:
      title: string
      slug:
        type: string
        modifiers: [unique]
      content: text
      is_published:
        type: boolean
      published_at:
        type: datetime
        modifiers: [nullable]
    relations:
      - type: belongsTo
        model: Category
      - type: belongsTo
        model: User
        foreign_key: author_id
      - type: hasMany
        model: Comment

  Comment:
    fields:
      body: text
      is_approved:
        type: boolean
    relations:
      - type: belongsTo
        model: Post
      - type: belongsTo
        model: User
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ]);

        // Check alle modellen zijn aangemaakt
        $this->assertFileExists($this->testOutputPath . '/Models/Category.php');
        $this->assertFileExists($this->testOutputPath . '/Models/Post.php');
        $this->assertFileExists($this->testOutputPath . '/Models/Comment.php');

        // Check controllers
        $this->assertFileExists($this->testOutputPath . '/Controllers/CategoryController.php');
        $this->assertFileExists($this->testOutputPath . '/Controllers/PostController.php');
        $this->assertFileExists($this->testOutputPath . '/Controllers/CommentController.php');

        // Check views
        $this->assertDirectoryExists($this->testOutputPath . '/views/categories');
        $this->assertDirectoryExists($this->testOutputPath . '/views/posts');
        $this->assertDirectoryExists($this->testOutputPath . '/views/comments');

        // Check relaties in Post model
        $postContent = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('public function category()', $postContent);
        $this->assertStringContainsString('public function user()', $postContent);
        $this->assertStringContainsString('public function comments()', $postContent);
    }

    #[Test]
    public function it_outputs_progress_for_each_model(): void
    {
        $yaml = <<<YAML
models:
  First:
    fields:
      name: string
  Second:
    fields:
      title: string
YAML;

        File::put($this->yamlPath, $yaml);

        $this->artisan('make:crud-yaml', [
            'file' => $this->yamlPath,
        ])
        ->expectsOutputToContain('First')
        ->expectsOutputToContain('Second');
    }
}
