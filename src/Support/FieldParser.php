<?php

namespace Mosweed\CrudGenerator\Support;

use Illuminate\Support\Str;

class FieldParser
{
    /**
     * Parse een fields string naar een array.
     * 
     * Formaat: naam:type:modifier1:modifier2,naam2:type2
     * Voorbeeld: title:string:nullable,body:text,status:enum:active,inactive
     */
    public static function parse(string $fieldsString): array
    {
        $fields = [];
        $fieldDefinitions = explode(',', $fieldsString);

        foreach ($fieldDefinitions as $definition) {
            $parts = explode(':', trim($definition));
            
            if (count($parts) < 2) {
                continue;
            }

            $name = $parts[0];
            $type = $parts[1];
            $modifiers = array_slice($parts, 2);

            // Check voor enum values
            $enumValues = [];
            if ($type === 'enum' && count($modifiers) > 0) {
                $enumValues = $modifiers;
                $modifiers = [];
            }

            $fields[] = [
                'name' => $name,
                'type' => $type,
                'modifiers' => $modifiers,
                'enum_values' => $enumValues,
            ];
        }

        return $fields;
    }

    /**
     * Genereer migration column definitie.
     */
    public static function toMigrationColumn(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $modifiers = $field['modifiers'] ?? [];

        $column = match ($type) {
            'string' => "\$table->string('{$name}')",
            'text' => "\$table->text('{$name}')",
            'integer' => "\$table->integer('{$name}')",
            'bigInteger' => "\$table->bigInteger('{$name}')",
            'unsignedBigInteger' => "\$table->unsignedBigInteger('{$name}')",
            'float' => "\$table->float('{$name}')",
            'decimal' => "\$table->decimal('{$name}', 10, 2)",
            'boolean' => "\$table->boolean('{$name}')",
            'date' => "\$table->date('{$name}')",
            'datetime' => "\$table->dateTime('{$name}')",
            'timestamp' => "\$table->timestamp('{$name}')",
            'time' => "\$table->time('{$name}')",
            'json' => "\$table->json('{$name}')",
            'enum' => self::buildEnumColumn($name, $field['enum_values'] ?? []),
            'foreignId' => "\$table->foreignId('{$name}')",
            default => "\$table->string('{$name}')",
        };

        // Voeg modifiers toe
        foreach ($modifiers as $modifier) {
            $column .= match ($modifier) {
                'nullable' => '->nullable()',
                'unique' => '->unique()',
                'index' => '->index()',
                'unsigned' => '->unsigned()',
                default => str_starts_with($modifier, 'default:') 
                    ? "->default('" . substr($modifier, 8) . "')"
                    : '',
            };
        }

        return $column . ';';
    }

    protected static function buildEnumColumn(string $name, array $values): string
    {
        if (empty($values)) {
            $values = ['pending', 'active', 'inactive'];
        }
        $valuesString = implode("', '", $values);
        return "\$table->enum('{$name}', ['{$valuesString}'])";
    }

    /**
     * Genereer factory faker definitie.
     */
    public static function toFakerDefinition(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];

        return match ($type) {
            'string' => self::guessFakerByName($name),
            'text' => 'fake()->paragraphs(3, true)',
            'integer' => 'fake()->numberBetween(1, 100)',
            'bigInteger' => 'fake()->numberBetween(1, 1000000)',
            'float', 'decimal' => 'fake()->randomFloat(2, 0, 1000)',
            'boolean' => 'fake()->boolean()',
            'date' => 'fake()->date()',
            'datetime', 'timestamp' => 'fake()->dateTime()',
            'time' => 'fake()->time()',
            'json' => 'json_encode([\'key\' => fake()->word()])',
            'enum' => "fake()->randomElement(['" . implode("', '", $field['enum_values'] ?? ['pending', 'active']) . "'])",
            default => 'fake()->word()',
        };
    }

    protected static function guessFakerByName(string $name): string
    {
        return match (true) {
            str_contains($name, 'email') => 'fake()->unique()->safeEmail()',
            str_contains($name, 'name') => 'fake()->name()',
            str_contains($name, 'first_name') => 'fake()->firstName()',
            str_contains($name, 'last_name') => 'fake()->lastName()',
            str_contains($name, 'phone') => 'fake()->phoneNumber()',
            str_contains($name, 'address') => 'fake()->address()',
            str_contains($name, 'city') => 'fake()->city()',
            str_contains($name, 'country') => 'fake()->country()',
            str_contains($name, 'zip') || str_contains($name, 'postal') => 'fake()->postcode()',
            str_contains($name, 'url') || str_contains($name, 'website') => 'fake()->url()',
            str_contains($name, 'image') || str_contains($name, 'avatar') => 'fake()->imageUrl()',
            str_contains($name, 'title') => 'fake()->sentence(4)',
            str_contains($name, 'slug') => 'fake()->slug()',
            str_contains($name, 'description') || str_contains($name, 'summary') => 'fake()->paragraph()',
            str_contains($name, 'price') || str_contains($name, 'amount') || str_contains($name, 'cost') => 'fake()->randomFloat(2, 10, 1000)',
            str_contains($name, 'quantity') || str_contains($name, 'stock') => 'fake()->numberBetween(0, 100)',
            str_contains($name, 'color') => 'fake()->hexColor()',
            str_contains($name, 'uuid') => 'fake()->uuid()',
            str_contains($name, 'ip') => 'fake()->ipv4()',
            default => 'fake()->sentence()',
        };
    }

    /**
     * Genereer validatie rules.
     */
    public static function toValidationRules(array $field, bool $isUpdate = false): string
    {
        $name = $field['name'];
        $type = $field['type'];
        $modifiers = $field['modifiers'] ?? [];

        $rules = [];

        // Required of nullable
        if (in_array('nullable', $modifiers)) {
            $rules[] = 'nullable';
        } else {
            $rules[] = $isUpdate ? 'sometimes' : 'required';
        }

        // Type-specifieke rules
        $rules[] = match ($type) {
            'string' => 'string|max:255',
            'text' => 'string',
            'integer', 'bigInteger' => 'integer',
            'float', 'decimal' => 'numeric',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime', 'timestamp' => 'date',
            'time' => 'date_format:H:i:s',
            'json' => 'json',
            'enum' => "in:" . implode(',', $field['enum_values'] ?? []),
            default => 'string|max:255',
        };

        // Unique modifier
        if (in_array('unique', $modifiers)) {
            $rules[] = 'unique:' . Str::snake(Str::pluralStudly($name));
        }

        // Speciale naam-gebaseerde rules
        if (str_contains($name, 'email')) {
            $rules[] = 'email';
        }
        if (str_contains($name, 'url') || str_contains($name, 'website')) {
            $rules[] = 'url';
        }

        return implode('|', $rules);
    }

    /**
     * Bepaal het form input type.
     */
    public static function toInputType(array $field): string
    {
        $name = $field['name'];
        $type = $field['type'];

        return match (true) {
            str_contains($name, 'email') => 'email',
            str_contains($name, 'password') => 'password',
            str_contains($name, 'url') || str_contains($name, 'website') => 'url',
            str_contains($name, 'phone') => 'tel',
            str_contains($name, 'color') => 'color',
            $type === 'text' => 'textarea',
            $type === 'boolean' => 'checkbox',
            $type === 'integer' || $type === 'bigInteger' || $type === 'float' || $type === 'decimal' => 'number',
            $type === 'date' => 'date',
            $type === 'datetime' || $type === 'timestamp' => 'datetime-local',
            $type === 'time' => 'time',
            $type === 'enum' => 'select',
            default => 'text',
        };
    }
}
