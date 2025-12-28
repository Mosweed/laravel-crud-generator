<?php

namespace Mosweed\CrudGenerator\Tests\Unit;

use Mosweed\CrudGenerator\Support\FieldParser;
use Mosweed\CrudGenerator\Tests\TestCase;

class FieldParserTest extends TestCase
{
    /** @test */
    public function it_parses_simple_field_definition(): void
    {
        $result = FieldParser::parse('title:string');

        $this->assertCount(1, $result);
        $this->assertEquals('title', $result[0]['name']);
        $this->assertEquals('string', $result[0]['type']);
        $this->assertEmpty($result[0]['modifiers']);
    }

    /** @test */
    public function it_parses_multiple_fields(): void
    {
        $result = FieldParser::parse('title:string,body:text,views:integer');

        $this->assertCount(3, $result);
        $this->assertEquals('title', $result[0]['name']);
        $this->assertEquals('body', $result[1]['name']);
        $this->assertEquals('views', $result[2]['name']);
    }

    /** @test */
    public function it_parses_field_with_modifiers(): void
    {
        $result = FieldParser::parse('email:string:unique:nullable');

        $this->assertCount(1, $result);
        $this->assertEquals('email', $result[0]['name']);
        $this->assertEquals('string', $result[0]['type']);
        $this->assertContains('unique', $result[0]['modifiers']);
        $this->assertContains('nullable', $result[0]['modifiers']);
    }

    /** @test */
    public function it_parses_enum_field_with_values(): void
    {
        $result = FieldParser::parse('status:enum:draft:published:archived');

        $this->assertCount(1, $result);
        $this->assertEquals('status', $result[0]['name']);
        $this->assertEquals('enum', $result[0]['type']);
        $this->assertEquals(['draft', 'published', 'archived'], $result[0]['enum_values']);
    }

    /** @test */
    public function it_generates_migration_column_for_string(): void
    {
        $field = ['name' => 'title', 'type' => 'string', 'modifiers' => []];
        $result = FieldParser::toMigrationColumn($field);

        $this->assertEquals("\$table->string('title');", $result);
    }

    /** @test */
    public function it_generates_migration_column_with_nullable(): void
    {
        $field = ['name' => 'bio', 'type' => 'text', 'modifiers' => ['nullable']];
        $result = FieldParser::toMigrationColumn($field);

        $this->assertEquals("\$table->text('bio')->nullable();", $result);
    }

    /** @test */
    public function it_generates_migration_column_with_unique(): void
    {
        $field = ['name' => 'email', 'type' => 'string', 'modifiers' => ['unique']];
        $result = FieldParser::toMigrationColumn($field);

        $this->assertEquals("\$table->string('email')->unique();", $result);
    }

    /** @test */
    public function it_generates_migration_column_for_enum(): void
    {
        $field = ['name' => 'status', 'type' => 'enum', 'enum_values' => ['active', 'inactive']];
        $result = FieldParser::toMigrationColumn($field);

        $this->assertEquals("\$table->enum('status', ['active', 'inactive']);", $result);
    }

    /** @test */
    public function it_generates_migration_column_for_decimal(): void
    {
        $field = ['name' => 'price', 'type' => 'decimal', 'modifiers' => []];
        $result = FieldParser::toMigrationColumn($field);

        $this->assertEquals("\$table->decimal('price', 10, 2);", $result);
    }

    /** @test */
    public function it_generates_faker_definition_for_string(): void
    {
        $field = ['name' => 'title', 'type' => 'string'];
        $result = FieldParser::toFakerDefinition($field);

        $this->assertEquals("fake()->sentence(4)", $result);
    }

    /** @test */
    public function it_generates_faker_definition_for_email(): void
    {
        $field = ['name' => 'email', 'type' => 'string'];
        $result = FieldParser::toFakerDefinition($field);

        $this->assertEquals("fake()->unique()->safeEmail()", $result);
    }

    /** @test */
    public function it_generates_faker_definition_for_price(): void
    {
        $field = ['name' => 'price', 'type' => 'string'];
        $result = FieldParser::toFakerDefinition($field);

        $this->assertEquals("fake()->randomFloat(2, 10, 1000)", $result);
    }

    /** @test */
    public function it_generates_faker_definition_for_boolean(): void
    {
        $field = ['name' => 'is_active', 'type' => 'boolean'];
        $result = FieldParser::toFakerDefinition($field);

        $this->assertEquals("fake()->boolean()", $result);
    }

    /** @test */
    public function it_generates_faker_definition_for_enum(): void
    {
        $field = ['name' => 'status', 'type' => 'enum', 'enum_values' => ['draft', 'published']];
        $result = FieldParser::toFakerDefinition($field);

        $this->assertEquals("fake()->randomElement(['draft', 'published'])", $result);
    }

    /** @test */
    public function it_generates_validation_rules_for_required_string(): void
    {
        $field = ['name' => 'title', 'type' => 'string', 'modifiers' => []];
        $result = FieldParser::toValidationRules($field);

        $this->assertStringContainsString('required', $result);
        $this->assertStringContainsString('string', $result);
        $this->assertStringContainsString('max:255', $result);
    }

    /** @test */
    public function it_generates_validation_rules_for_nullable_field(): void
    {
        $field = ['name' => 'bio', 'type' => 'text', 'modifiers' => ['nullable']];
        $result = FieldParser::toValidationRules($field);

        $this->assertStringContainsString('nullable', $result);
        $this->assertStringNotContainsString('required', $result);
    }

    /** @test */
    public function it_generates_validation_rules_for_integer(): void
    {
        $field = ['name' => 'age', 'type' => 'integer', 'modifiers' => []];
        $result = FieldParser::toValidationRules($field);

        $this->assertStringContainsString('integer', $result);
    }

    /** @test */
    public function it_generates_validation_rules_for_enum(): void
    {
        $field = ['name' => 'status', 'type' => 'enum', 'modifiers' => [], 'enum_values' => ['active', 'inactive']];
        $result = FieldParser::toValidationRules($field);

        $this->assertStringContainsString('in:active,inactive', $result);
    }

    /** @test */
    public function it_generates_validation_rules_for_update(): void
    {
        $field = ['name' => 'title', 'type' => 'string', 'modifiers' => []];
        $result = FieldParser::toValidationRules($field, true);

        $this->assertStringContainsString('sometimes', $result);
        $this->assertStringNotContainsString('required', $result);
    }

    /** @test */
    public function it_determines_correct_input_type_for_email(): void
    {
        $field = ['name' => 'email', 'type' => 'string'];
        $result = FieldParser::toInputType($field);

        $this->assertEquals('email', $result);
    }

    /** @test */
    public function it_determines_correct_input_type_for_password(): void
    {
        $field = ['name' => 'password', 'type' => 'string'];
        $result = FieldParser::toInputType($field);

        $this->assertEquals('password', $result);
    }

    /** @test */
    public function it_determines_correct_input_type_for_text(): void
    {
        $field = ['name' => 'description', 'type' => 'text'];
        $result = FieldParser::toInputType($field);

        $this->assertEquals('textarea', $result);
    }

    /** @test */
    public function it_determines_correct_input_type_for_boolean(): void
    {
        $field = ['name' => 'is_active', 'type' => 'boolean'];
        $result = FieldParser::toInputType($field);

        $this->assertEquals('checkbox', $result);
    }

    /** @test */
    public function it_determines_correct_input_type_for_date(): void
    {
        $field = ['name' => 'birth_date', 'type' => 'date'];
        $result = FieldParser::toInputType($field);

        $this->assertEquals('date', $result);
    }

    /** @test */
    public function it_determines_correct_input_type_for_enum(): void
    {
        $field = ['name' => 'status', 'type' => 'enum'];
        $result = FieldParser::toInputType($field);

        $this->assertEquals('select', $result);
    }
}
