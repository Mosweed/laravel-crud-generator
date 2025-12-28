<?php

namespace Mosweed\CrudGenerator\Tests\Unit;

use Mosweed\CrudGenerator\Generators\RequestGenerator;
use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class RequestGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_store_request_file(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $result = $generator->generate();

        $this->assertTrue($result);
        $this->assertFileExists($this->testOutputPath . '/Requests/StorePostRequest.php');
    }

    /** @test */
    public function it_generates_update_request_file(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $this->assertFileExists($this->testOutputPath . '/Requests/UpdatePostRequest.php');
    }

    /** @test */
    public function it_generates_store_request_with_required_rules(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StorePostRequest.php');
        $this->assertStringContainsString("'title' => 'required", $content);
    }

    /** @test */
    public function it_generates_update_request_with_sometimes_rules(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/UpdatePostRequest.php');
        $this->assertStringContainsString("'title' => 'sometimes", $content);
    }

    /** @test */
    public function it_generates_request_with_nullable_rules(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'subtitle', 'type' => 'string', 'modifiers' => ['nullable']],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StorePostRequest.php');
        $this->assertStringContainsString("'subtitle' => 'nullable", $content);
    }

    /** @test */
    public function it_generates_request_with_type_rules(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
                ['name' => 'views', 'type' => 'integer', 'modifiers' => []],
                ['name' => 'is_published', 'type' => 'boolean', 'modifiers' => []],
                ['name' => 'published_at', 'type' => 'date', 'modifiers' => ['nullable']],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StorePostRequest.php');
        
        $this->assertStringContainsString('string|max:255', $content);
        $this->assertStringContainsString('integer', $content);
        $this->assertStringContainsString('boolean', $content);
        $this->assertStringContainsString('date', $content);
    }

    /** @test */
    public function it_generates_request_with_enum_rules(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'status', 'type' => 'enum', 'modifiers' => [], 'enum_values' => ['draft', 'published']],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StorePostRequest.php');
        $this->assertStringContainsString('in:draft,published', $content);
    }

    /** @test */
    public function it_generates_request_with_custom_messages(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StorePostRequest.php');
        
        $this->assertStringContainsString('public function messages()', $content);
        $this->assertStringContainsString("'title.required'", $content);
    }

    /** @test */
    public function it_generates_request_with_numeric_rules(): void
    {
        $config = [
            'model_name' => 'Product',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'price', 'type' => 'decimal', 'modifiers' => []],
                ['name' => 'weight', 'type' => 'float', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StoreProductRequest.php');
        $this->assertStringContainsString('numeric', $content);
    }

    /** @test */
    public function it_generates_request_that_authorizes(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StorePostRequest.php');
        
        $this->assertStringContainsString('public function authorize()', $content);
        $this->assertStringContainsString('return true;', $content);
    }

    /** @test */
    public function it_generates_request_with_json_rules(): void
    {
        $config = [
            'model_name' => 'Setting',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'data', 'type' => 'json', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new RequestGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/Requests/StoreSettingRequest.php');
        $this->assertStringContainsString('json', $content);
    }
}
