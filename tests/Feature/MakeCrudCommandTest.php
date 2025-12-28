<?php

namespace Mosweed\CrudGenerator\Tests\Feature;

use Mosweed\CrudGenerator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class MakeCrudCommandTest extends TestCase
{
    /** @test */
    public function it_can_run_make_crud_command(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Article',
            '--fields' => 'title:string,content:text',
        ])
        ->assertExitCode(0);
    }

    /** @test */
    public function it_generates_all_files(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Product',
            '--fields' => 'name:string,price:decimal',
        ]);

        // Check alle bestanden
        $this->assertFileExists($this->testOutputPath . '/Models/Product.php');
        $this->assertFileExists($this->testOutputPath . '/Controllers/ProductController.php');
        $this->assertFileExists($this->testOutputPath . '/Requests/StoreProductRequest.php');
        $this->assertFileExists($this->testOutputPath . '/Requests/UpdateProductRequest.php');
        $this->assertFileExists($this->testOutputPath . '/Resources/ProductResource.php');
        
        $migrations = File::glob($this->testOutputPath . '/migrations/*_create_products_table.php');
        $this->assertCount(1, $migrations);
        
        $this->assertFileExists($this->testOutputPath . '/factories/ProductFactory.php');
        $this->assertFileExists($this->testOutputPath . '/seeders/ProductSeeder.php');
        $this->assertFileExists($this->testOutputPath . '/views/products/index.blade.php');
    }

    /** @test */
    public function it_generates_crud_with_relations(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Comment',
            '--fields' => 'body:text',
            '--relations' => 'belongsTo:Post,belongsTo:User',
        ]);

        $modelContent = File::get($this->testOutputPath . '/Models/Comment.php');
        
        $this->assertStringContainsString('public function post()', $modelContent);
        $this->assertStringContainsString('public function user()', $modelContent);
        $this->assertStringContainsString('belongsTo', $modelContent);
    }

    /** @test */
    public function it_generates_api_only_controller(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Task',
            '--fields' => 'title:string',
            '--api' => true,
        ]);

        $controllerContent = File::get($this->testOutputPath . '/Controllers/TaskController.php');
        
        $this->assertStringContainsString('JsonResponse', $controllerContent);
        $this->assertStringNotContainsString('public function create()', $controllerContent);
        
        // Views should not exist for API
        $this->assertDirectoryDoesNotExist($this->testOutputPath . '/views/tasks');
    }

    /** @test */
    public function it_adds_routes_to_web_file(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Category',
            '--fields' => 'name:string',
        ]);

        $routeContent = File::get($this->testOutputPath . '/routes/web.php');
        
        $this->assertStringContainsString('CategoryController', $routeContent);
        $this->assertStringContainsString("Route::resource('categories'", $routeContent);
    }

    /** @test */
    public function it_adds_routes_to_api_file_when_api_only(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Item',
            '--fields' => 'name:string',
            '--api' => true,
        ]);

        $routeContent = File::get($this->testOutputPath . '/routes/api.php');
        
        $this->assertStringContainsString('ItemController', $routeContent);
        $this->assertStringContainsString("Route::apiResource('items'", $routeContent);
    }

    /** @test */
    public function it_handles_nested_model_names(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Blog/Post',
            '--fields' => 'title:string',
        ]);

        $this->assertFileExists($this->testOutputPath . '/Models/Post.php');
        
        $modelContent = File::get($this->testOutputPath . '/Models/Post.php');
        $this->assertStringContainsString('namespace App\\Models\\Blog;', $modelContent);
    }

    /** @test */
    public function it_generates_enum_fields_correctly(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Order',
            '--fields' => 'status:enum:pending:processing:completed',
        ]);

        // Check migration
        $migrations = File::glob($this->testOutputPath . '/migrations/*_create_orders_table.php');
        $migrationContent = File::get($migrations[0]);
        $this->assertStringContainsString("enum('status', ['pending', 'processing', 'completed'])", $migrationContent);

        // Check factory
        $factoryContent = File::get($this->testOutputPath . '/factories/OrderFactory.php');
        $this->assertStringContainsString("randomElement(['pending', 'processing', 'completed'])", $factoryContent);

        // Check view form
        $formContent = File::get($this->testOutputPath . '/views/orders/_form.blade.php');
        $this->assertStringContainsString('<select', $formContent);
    }

    /** @test */
    public function it_generates_nullable_fields_correctly(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Profile',
            '--fields' => 'bio:text:nullable,website:string:nullable',
        ]);

        // Check migration
        $migrations = File::glob($this->testOutputPath . '/migrations/*_create_profiles_table.php');
        $migrationContent = File::get($migrations[0]);
        $this->assertStringContainsString('->nullable()', $migrationContent);

        // Check request
        $requestContent = File::get($this->testOutputPath . '/Requests/StoreProfileRequest.php');
        $this->assertStringContainsString('nullable', $requestContent);
    }

    /** @test */
    public function it_generates_belongs_to_many_relations(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Article',
            '--fields' => 'title:string',
            '--relations' => 'belongsToMany:Tag',
        ]);

        $modelContent = File::get($this->testOutputPath . '/Models/Article.php');
        
        $this->assertStringContainsString('public function tags()', $modelContent);
        $this->assertStringContainsString('belongsToMany', $modelContent);
    }

    /** @test */
    public function it_handles_force_option(): void
    {
        // First run
        $this->artisan('make:crud', [
            'name' => 'Widget',
            '--fields' => 'name:string',
        ]);

        $originalContent = File::get($this->testOutputPath . '/Models/Widget.php');

        // Second run without force should not overwrite
        $this->artisan('make:crud', [
            'name' => 'Widget',
            '--fields' => 'name:string,description:text',
        ]);

        $afterContent = File::get($this->testOutputPath . '/Models/Widget.php');
        $this->assertEquals($originalContent, $afterContent);

        // Third run with force should overwrite
        $this->artisan('make:crud', [
            'name' => 'Widget',
            '--fields' => 'name:string,description:text',
            '--force' => true,
        ]);

        $forcedContent = File::get($this->testOutputPath . '/Models/Widget.php');
        $this->assertStringContainsString("'description'", $forcedContent);
    }

    /** @test */
    public function it_outputs_success_message(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Event',
            '--fields' => 'name:string',
        ])
        ->expectsOutputToContain('CRUD succesvol gegenereerd');
    }

    /** @test */
    public function it_outputs_next_steps(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Ticket',
            '--fields' => 'title:string',
        ])
        ->expectsOutputToContain('Volgende stappen');
    }

    /** @test */
    public function it_generates_correct_view_folder_for_plural_models(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Category',
            '--fields' => 'name:string',
        ]);

        $this->assertDirectoryExists($this->testOutputPath . '/views/categories');
    }

    /** @test */
    public function it_generates_factory_with_belongs_to_relation(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Review',
            '--fields' => 'rating:integer,comment:text',
            '--relations' => 'belongsTo:Product,belongsTo:User',
        ]);

        $factoryContent = File::get($this->testOutputPath . '/factories/ReviewFactory.php');
        
        $this->assertStringContainsString('Product::factory()', $factoryContent);
        $this->assertStringContainsString('User::factory()', $factoryContent);
    }

    /** @test */
    public function it_generates_seeder_correctly(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Tag',
            '--fields' => 'name:string',
        ]);

        $seederContent = File::get($this->testOutputPath . '/seeders/TagSeeder.php');
        
        $this->assertStringContainsString('use App\\Models\\Tag;', $seederContent);
        $this->assertStringContainsString('Tag::factory()', $seederContent);
    }

    /** @test */
    public function it_generates_resource_with_fields(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Book',
            '--fields' => 'title:string,isbn:string,pages:integer',
        ]);

        $resourceContent = File::get($this->testOutputPath . '/Resources/BookResource.php');
        
        $this->assertStringContainsString("'title' => \$this->title", $resourceContent);
        $this->assertStringContainsString("'isbn' => \$this->isbn", $resourceContent);
        $this->assertStringContainsString("'pages' => \$this->pages", $resourceContent);
        $this->assertStringContainsString("'created_at'", $resourceContent);
        $this->assertStringContainsString("'updated_at'", $resourceContent);
    }

    /** @test */
    public function it_generates_resource_with_relations(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Chapter',
            '--fields' => 'title:string',
            '--relations' => 'belongsTo:Book,hasMany:Page',
        ]);

        $resourceContent = File::get($this->testOutputPath . '/Resources/ChapterResource.php');
        
        $this->assertStringContainsString('whenLoaded', $resourceContent);
        $this->assertStringContainsString("'book'", $resourceContent);
        $this->assertStringContainsString("'pages'", $resourceContent);
    }

    /** @test */
    public function it_handles_custom_foreign_key(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Article',
            '--fields' => 'title:string',
            '--relations' => 'belongsTo:User:author_id',
        ]);

        // Check model
        $modelContent = File::get($this->testOutputPath . '/Models/Article.php');
        $this->assertStringContainsString("'author_id'", $modelContent);

        // Check migration
        $migrations = File::glob($this->testOutputPath . '/migrations/*_create_articles_table.php');
        $migrationContent = File::get($migrations[0]);
        $this->assertStringContainsString("foreignId('author_id')", $migrationContent);

        // Check factory
        $factoryContent = File::get($this->testOutputPath . '/factories/ArticleFactory.php');
        $this->assertStringContainsString("'author_id' => \\App\\Models\\User::factory()", $factoryContent);
    }

    /** @test */
    public function it_generates_controller_with_eager_loading(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Invoice',
            '--fields' => 'number:string,total:decimal',
            '--relations' => 'belongsTo:Customer,hasMany:InvoiceLine',
        ]);

        $controllerContent = File::get($this->testOutputPath . '/Controllers/InvoiceController.php');
        
        $this->assertStringContainsString("->with([", $controllerContent);
        $this->assertStringContainsString("'customer'", $controllerContent);
        $this->assertStringContainsString("'invoiceLines'", $controllerContent);
    }

    /** @test */
    public function it_generates_model_with_correct_casts(): void
    {
        $this->artisan('make:crud', [
            'name' => 'Setting',
            '--fields' => 'is_active:boolean,config:json,amount:decimal,expires_at:datetime',
        ]);

        $modelContent = File::get($this->testOutputPath . '/Models/Setting.php');
        
        $this->assertStringContainsString("'is_active' => 'boolean'", $modelContent);
        $this->assertStringContainsString("'config' => 'array'", $modelContent);
        $this->assertStringContainsString("'amount' => 'decimal:2'", $modelContent);
        $this->assertStringContainsString("'expires_at' => 'datetime'", $modelContent);
    }

    /** @test */
    public function it_handles_multiple_modifiers(): void
    {
        $this->artisan('make:crud', [
            'name' => 'User',
            '--fields' => 'email:string:unique:nullable,age:integer:unsigned',
        ]);

        $migrations = File::glob($this->testOutputPath . '/migrations/*_create_users_table.php');
        $migrationContent = File::get($migrations[0]);
        
        $this->assertStringContainsString('->unique()', $migrationContent);
        $this->assertStringContainsString('->nullable()', $migrationContent);
        $this->assertStringContainsString('->unsigned()', $migrationContent);
    }
}
