<?php

namespace Mosweed\CrudGenerator\Tests\Unit;

use Mosweed\CrudGenerator\Generators\FactoryGenerator;
use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class FactoryGeneratorTest extends TestCase
{
    /** @test */
    public function it_generates_factory_file(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $result = $generator->generate();

        $this->assertTrue($result);
        $this->assertFileExists($this->testOutputPath . '/factories/PostFactory.php');
    }

    /** @test */
    public function it_generates_factory_with_correct_model_reference(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        
        $this->assertStringContainsString('use App\\Models\\Post;', $content);
        $this->assertStringContainsString('protected $model = Post::class;', $content);
    }

    /** @test */
    public function it_generates_factory_with_faker_definitions(): void
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

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        
        $this->assertStringContainsString("'title' =>", $content);
        $this->assertStringContainsString("'body' =>", $content);
        $this->assertStringContainsString("'views' =>", $content);
        $this->assertStringContainsString('fake()->', $content);
    }

    /** @test */
    public function it_generates_factory_with_email_faker(): void
    {
        $config = [
            'model_name' => 'User',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'email', 'type' => 'string', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/UserFactory.php');
        $this->assertStringContainsString('safeEmail()', $content);
    }

    /** @test */
    public function it_generates_factory_with_boolean_faker(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'is_published', 'type' => 'boolean', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        $this->assertStringContainsString('fake()->boolean()', $content);
    }

    /** @test */
    public function it_generates_factory_with_date_faker(): void
    {
        $config = [
            'model_name' => 'Event',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'event_date', 'type' => 'date', 'modifiers' => []],
                ['name' => 'starts_at', 'type' => 'datetime', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/EventFactory.php');
        $this->assertStringContainsString('fake()->date()', $content);
        $this->assertStringContainsString('fake()->dateTime()', $content);
    }

    /** @test */
    public function it_generates_factory_with_enum_faker(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'status', 'type' => 'enum', 'modifiers' => [], 'enum_values' => ['draft', 'published', 'archived']],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        $this->assertStringContainsString("fake()->randomElement(['draft', 'published', 'archived'])", $content);
    }

    /** @test */
    public function it_generates_factory_with_related_model_for_belongs_to(): void
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

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        $this->assertStringContainsString("'user_id' => \\App\\Models\\User::factory()", $content);
    }

    /** @test */
    public function it_generates_factory_with_custom_foreign_key(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [],
            'relations' => [
                ['type' => 'belongsTo', 'model' => 'User', 'foreign_key' => 'author_id'],
            ],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        $this->assertStringContainsString("'author_id' => \\App\\Models\\User::factory()", $content);
    }

    /** @test */
    public function it_generates_factory_with_states_for_enum(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'status', 'type' => 'enum', 'modifiers' => [], 'enum_values' => ['draft', 'published']],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        
        $this->assertStringContainsString('public function draft()', $content);
        $this->assertStringContainsString('public function published()', $content);
    }

    /** @test */
    public function it_generates_factory_with_states_for_boolean(): void
    {
        $config = [
            'model_name' => 'Post',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'is_featured', 'type' => 'boolean', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/PostFactory.php');
        
        $this->assertStringContainsString('public function isFeatured()', $content);
        $this->assertStringContainsString('public function notIsFeatured()', $content);
    }

    /** @test */
    public function it_generates_factory_with_price_faker(): void
    {
        $config = [
            'model_name' => 'Product',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'price', 'type' => 'decimal', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/ProductFactory.php');
        $this->assertStringContainsString('randomFloat(2', $content);
    }

    /** @test */
    public function it_generates_factory_with_json_faker(): void
    {
        $config = [
            'model_name' => 'Setting',
            'model_namespace' => 'App\\Models',
            'fields' => [
                ['name' => 'metadata', 'type' => 'json', 'modifiers' => []],
            ],
            'relations' => [],
        ];

        $generator = new FactoryGenerator($config);
        $generator->generate();

        $content = File::get($this->testOutputPath . '/factories/SettingFactory.php');
        $this->assertStringContainsString('json_encode(', $content);
    }
}
