<?php

namespace Mosweed\CrudGenerator\Tests\Unit;

use Mosweed\CrudGenerator\Generators\ViewGenerator;
use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ViewGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_all_view_files(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $result = $generator->generate();

        $this->assertTrue($result);
        $this->assertFileExists($this->testOutputPath . '/views/posts/index.blade.php');
        $this->assertFileExists($this->testOutputPath . '/views/posts/create.blade.php');
        $this->assertFileExists($this->testOutputPath . '/views/posts/edit.blade.php');
        $this->assertFileExists($this->testOutputPath . '/views/posts/show.blade.php');
        $this->assertFileExists($this->testOutputPath . '/views/posts/_form.blade.php');
    }

    /** @test */
    public function it_generates_index_view_with_table(): void
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

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/index.blade.php');
        
        $this->assertStringContainsString('<table', $content);
        $this->assertStringContainsString('Title', $content);
        $this->assertStringContainsString('Body', $content);
        $this->assertStringContainsString('$posts', $content);
    }

    /** @test */
    public function it_generates_index_view_with_search(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/index.blade.php');
        
        $this->assertStringContainsString('name="search"', $content);
        $this->assertStringContainsString('Zoeken', $content);
    }

    /** @test */
    public function it_generates_index_view_with_pagination(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/index.blade.php');
        $this->assertStringContainsString('->links()', $content);
    }

    /** @test */
    public function it_generates_index_view_with_action_buttons(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/index.blade.php');
        
        $this->assertStringContainsString('Bekijken', $content);
        $this->assertStringContainsString('Bewerken', $content);
        $this->assertStringContainsString('Verwijderen', $content);
    }

    /** @test */
    public function it_generates_create_view_with_form(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/create.blade.php');
        
        $this->assertStringContainsString('<form', $content);
        $this->assertStringContainsString('@csrf', $content);
        $this->assertStringContainsString("route('posts.store')", $content);
        $this->assertStringContainsString('@include(\'posts._form\')', $content);
    }

    /** @test */
    public function it_generates_edit_view_with_form(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/edit.blade.php');
        
        $this->assertStringContainsString('<form', $content);
        $this->assertStringContainsString('@csrf', $content);
        $this->assertStringContainsString('@method(\'PUT\')', $content);
        $this->assertStringContainsString("route('posts.update'", $content);
    }

    /** @test */
    public function it_generates_show_view_with_details(): void
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

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/show.blade.php');
        
        $this->assertStringContainsString('$post->title', $content);
        $this->assertStringContainsString('$post->body', $content);
        $this->assertStringContainsString('created_at', $content);
        $this->assertStringContainsString('updated_at', $content);
    }

    /** @test */
    public function it_generates_form_partial_with_fields(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
                ['name' => 'body', 'type' => 'text', 'modifiers' => ['nullable']],
                ['name' => 'is_published', 'type' => 'boolean', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/_form.blade.php');
        
        $this->assertStringContainsString('name="title"', $content);
        $this->assertStringContainsString('name="body"', $content);
        $this->assertStringContainsString('name="is_published"', $content);
    }

    /** @test */
    public function it_generates_form_with_text_input(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/_form.blade.php');
        $this->assertStringContainsString('type="text"', $content);
    }

    /** @test */
    public function it_generates_form_with_textarea(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'content', 'type' => 'text', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/_form.blade.php');
        $this->assertStringContainsString('<textarea', $content);
    }

    /** @test */
    public function it_generates_form_with_checkbox(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'is_featured', 'type' => 'boolean', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/_form.blade.php');
        $this->assertStringContainsString('type="checkbox"', $content);
    }

    /** @test */
    public function it_generates_form_with_select_for_enum(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'status', 'type' => 'enum', 'modifiers' => [], 'enum_values' => ['draft', 'published']],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/_form.blade.php');
        
        $this->assertStringContainsString('<select', $content);
        $this->assertStringContainsString('<option', $content);
        $this->assertStringContainsString('Draft', $content);
        $this->assertStringContainsString('Published', $content);
    }

    /** @test */
    public function it_generates_form_with_validation_errors(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/_form.blade.php');
        
        $this->assertStringContainsString("@error('title')", $content);
        $this->assertStringContainsString('$message', $content);
    }

    /** @test */
    public function it_generates_form_with_old_values(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/_form.blade.php');
        $this->assertStringContainsString("old('title'", $content);
    }

    /** @test */
    public function it_generates_views_with_tailwind_classes(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/views/posts/index.blade.php');
        
        $this->assertStringContainsString('bg-white', $content);
        $this->assertStringContainsString('rounded-md', $content);
        $this->assertStringContainsString('shadow', $content);
    }

    /** @test */
    public function it_generates_view_for_camel_case_model(): void
    {
        $config = [
            'model_name' => 'BlogPost',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new ViewGenerator($config);
        $generator->generate();

        $this->assertFileExists($this->testOutputPath . '/views/blog-posts/index.blade.php');
        
        $content = File::get($this->testOutputPath . '/views/blog-posts/index.blade.php');
        $this->assertStringContainsString('$blogPosts', $content);
    }
}
