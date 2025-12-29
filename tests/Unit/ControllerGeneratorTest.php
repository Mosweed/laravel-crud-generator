<?php

namespace Mosweed\CrudGenerator\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;

use Mosweed\CrudGenerator\Generators\ControllerGenerator;
use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ControllerGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_controller_file(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $result = $generator->generate();

        $this->assertTrue($result);
        $this->assertFileExists($this->testOutputPath . '/Controllers/PostController.php');
    }

    #[Test]
    public function it_generates_controller_with_correct_namespace(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        $this->assertStringContainsString('namespace App\\Http\\Controllers;', $content);
    }

    #[Test]
    public function it_generates_controller_with_all_crud_methods(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        $this->assertStringContainsString('public function index(', $content);
        $this->assertStringContainsString('public function create(', $content);
        $this->assertStringContainsString('public function store(', $content);
        $this->assertStringContainsString('public function show(', $content);
        $this->assertStringContainsString('public function edit(', $content);
        $this->assertStringContainsString('public function update(', $content);
        $this->assertStringContainsString('public function destroy(', $content);
    }

    #[Test]
    public function it_generates_controller_with_form_request_imports(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        $this->assertStringContainsString('use App\\Http\\Requests\\StorePostRequest;', $content);
        $this->assertStringContainsString('use App\\Http\\Requests\\UpdatePostRequest;', $content);
    }

    #[Test]
    public function it_generates_controller_with_model_import(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        $this->assertStringContainsString('use App\\Models\\Post;', $content);
    }

    #[Test]
    public function it_generates_controller_with_correct_variable_names(): void
    {
        $config = [
            'model_name' => 'BlogPost',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/BlogPostController.php');
        
        $this->assertStringContainsString('$blogPost', $content);
        $this->assertStringContainsString('$blogPosts', $content);
    }

    #[Test]
    public function it_generates_controller_with_view_references(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        $this->assertStringContainsString("view('posts.index'", $content);
        $this->assertStringContainsString("view('posts.create'", $content);
        $this->assertStringContainsString("view('posts.show'", $content);
        $this->assertStringContainsString("view('posts.edit'", $content);
    }

    #[Test]
    public function it_generates_controller_with_route_references(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        $this->assertStringContainsString("route('posts.show'", $content);
        $this->assertStringContainsString("route('posts.index'", $content);
    }

    #[Test]
    public function it_generates_api_controller_when_api_only(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
            'api_only' => true,
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        // API controller heeft geen create/edit methods
        $this->assertStringNotContainsString('public function create()', $content);
        $this->assertStringNotContainsString('public function edit(', $content);
        
        // API controller returnt JSON
        $this->assertStringContainsString('JsonResponse', $content);
        $this->assertStringContainsString('PostResource', $content);
    }

    #[Test]
    public function it_generates_controller_with_eager_loading_for_relations(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [
                ['type' => 'belongsTo', 'model' => 'User', 'foreign_key' => null],
                ['type' => 'hasMany', 'model' => 'Comment', 'foreign_key' => null],
            ],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        $this->assertStringContainsString("->with([", $content);
        $this->assertStringContainsString("'user'", $content);
        $this->assertStringContainsString("'comments'", $content);
    }

    #[Test]
    public function it_generates_controller_with_search_functionality(): void
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

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        $this->assertStringContainsString('$search = $request->get(\'search\')', $content);
        $this->assertStringContainsString("'title'", $content);
        $this->assertStringContainsString("'body'", $content);
    }

    #[Test]
    public function it_generates_controller_with_pagination(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        $this->assertStringContainsString('->paginate(', $content);
    }

    #[Test]
    public function it_generates_controller_with_sorting(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ControllerGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Controllers/PostController.php');
        
        $this->assertStringContainsString('$sortField', $content);
        $this->assertStringContainsString('$sortDirection', $content);
        $this->assertStringContainsString('orderBy(', $content);
    }
}
