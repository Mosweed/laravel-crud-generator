# Changelog

All notable changes to `laravel-crud-generator` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2024-XX-XX

### Added
- Initial release
- `make:crud` artisan command for generating complete CRUD
- `make:crud-yaml` command for bulk generation from YAML config
- Support for all common field types (string, text, integer, boolean, date, datetime, json, enum, etc.)
- Field modifiers (nullable, unique, index, unsigned, default)
- Relation support (belongsTo, hasMany, hasOne, belongsToMany, morphTo, morphMany)
- Interactive mode with Laravel Prompts
- Generated files:
  - Migrations with foreign keys
  - Eloquent Models with relations and casts
  - Controllers (Web & API)
  - Form Requests with validation
  - API Resources
  - Factories with smart Faker definitions
  - Seeders
  - Blade Views with Tailwind CSS
  - Routes (web & api)
- CSS theming system via CSS variables
- 17 pre-built color themes
- Customizable stubs
- Comprehensive test suite

### Security
- Input validation for all generated code
- CSRF protection in generated forms
- Proper escaping in Blade templates

## [0.1.0] - 2024-XX-XX

### Added
- Initial development release
