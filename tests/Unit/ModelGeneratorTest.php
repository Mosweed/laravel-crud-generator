<?php

namespace Mosweed\CrudGenerator\Tests\Unit;

use Mosweed\CrudGenerator\Generators\ModelGenerator;
use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ModelGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_model_file(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ModelGenerator($config);
        $result = $generator->generate();

        $this->assertTrue($result);
        $this->assertFileExists($this->testOutputPath . '/Models/Post.php');
    }

    /** @test */
    public function it_generates_model_with_correct_namespace(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('namespace App\\Models;', $content);
    }

    /** @test */
    public function it_generates_model_with_fillable_attributes(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
                ['name' => 'body', 'type' => 'text', 'modifiers' => []],
                ['name' => 'views', 'type' => 'integer', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString("'title'", $content);
        $this->assertStringContainsString("'body'", $content);
        $this->assertStringContainsString("'views'", $content);
    }

    /** @test */
    public function it_generates_model_with_casts(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'is_published', 'type' => 'boolean', 'modifiers' => []],
                ['name' => 'published_at', 'type' => 'datetime', 'modifiers' => []],
                ['name' => 'metadata', 'type' => 'json', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString("'is_published' => 'boolean'", $content);
        $this->assertStringContainsString("'published_at' => 'datetime'", $content);
        $this->assertStringContainsString("'metadata' => 'array'", $content);
    }

    /** @test */
    public function it_generates_model_with_belongs_to_relation(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [
                ['type' => 'belongsTo', 'model' => 'User', 'foreign_key' => null],
            ],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('public function user()', $content);
        $this->assertStringContainsString('return $this->belongsTo(', $content);
        $this->assertStringContainsString('User::class', $content);
    }

    /** @test */
    public function it_generates_model_with_has_many_relation(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [
                ['type' => 'hasMany', 'model' => 'Comment', 'foreign_key' => null],
            ],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('public function comments()', $content);
        $this->assertStringContainsString('return $this->hasMany(', $content);
    }

    /** @test */
    public function it_generates_model_with_belongs_to_many_relation(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [
                ['type' => 'belongsToMany', 'model' => 'Tag', 'foreign_key' => null],
            ],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('public function tags()', $content);
        $this->assertStringContainsString('return $this->belongsToMany(', $content);
    }

    /** @test */
    public function it_generates_model_with_has_factory_trait(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('use HasFactory;', $content);
        $this->assertStringContainsString('use Illuminate\Database\Eloquent\Factories\HasFactory;', $content);
    }

    /** @test */
    public function it_generates_model_with_soft_deletes_trait(): void
    {
        config(['crud-generator.soft_deletes' => true]);

        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('use SoftDeletes;', $content);
        $this->assertStringContainsString('use Illuminate\Database\Eloquent\SoftDeletes;', $content);
    }

    /** @test */
    public function it_generates_model_with_correct_table_name(): void
    {
        $config = [
            'model_name' => 'BlogPost',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/BlogPost.php');
        $this->assertStringContainsString("protected \$table = 'blog_posts';", $content);
    }

    /** @test */
    public function it_adds_foreign_key_to_fillable_for_belongs_to(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [
                ['type' => 'belongsTo', 'model' => 'Category', 'foreign_key' => null],
            ],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString("'category_id'", $content);
    }

    /** @test */
    public function it_generates_model_with_custom_foreign_key_in_relation(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [
                ['type' => 'belongsTo', 'model' => 'User', 'foreign_key' => 'author_id'],
            ],
        ];

        $generator = new ModelGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString("'author_id'", $content);
    }
}
