# Laravel CRUD Generator

[![Tests](https://github.com/mosweed/laravel-crud-generator/actions/workflows/tests.yml/badge.svg)](https://github.com/mosweed/laravel-crud-generator/actions/workflows/tests.yml)
[![Code Quality](https://github.com/mosweed/laravel-crud-generator/actions/workflows/code-quality.yml/badge.svg)](https://github.com/mosweed/laravel-crud-generator/actions/workflows/code-quality.yml)
[![Latest Stable Version](https://poser.pugx.org/mosweed/laravel-crud-generator/v/stable)](https://packagist.org/packages/mosweed/laravel-crud-generator)
[![Total Downloads](https://poser.pugx.org/mosweed/laravel-crud-generator/downloads)](https://packagist.org/packages/mosweed/laravel-crud-generator)
[![License](https://poser.pugx.org/mosweed/laravel-crud-generator/license)](https://packagist.org/packages/mosweed/laravel-crud-generator)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)

Een krachtige Laravel 12 package van **Mosweed** om automatisch complete CRUD functionaliteit te genereren inclusief migrations, seeders, factories, models, controllers, views, routes en relaties.

## 🚀 Features

- **Automatische generatie van:**
  - Migrations met foreign keys
  - Eloquent Models met relaties en casts
  - Controllers (Web & API)
  - Form Requests met validatie
  - API Resources
  - Factories met Faker
  - Seeders
  - Blade Views (Tailwind CSS)
  - Routes

- **Relatie ondersteuning:**
  - `belongsTo` (N:1)
  - `hasMany` (1:N)
  - `hasOne` (1:1)
  - `belongsToMany` (N:N met pivot tables)
  - `morphTo` / `morphMany` (Polymorphic)

- **Flexibele configuratie:**
  - Interactieve modus met prompts
  - YAML-gebaseerde bulk generatie
  - Aanpasbare stubs
  - Soft deletes standaard ingeschakeld

## 🎨 Thema's & Kleuren (via Tailwind CSS)

De kleuren worden beheerd via CSS variabelen, waardoor je ze eenvoudig kunt aanpassen via een CSS bestand.

### Installatie CSS

```bash
# Publiceer de CSS bestanden
php artisan vendor:publish --tag=crud-generator-assets
```

Dit maakt de volgende bestanden aan:
- `resources/css/vendor/crud-generator/crud-theme.css` - Kleurvariabelen
- `resources/css/vendor/crud-generator/crud-components.css` - Component classes

### Toevoegen aan je app.css

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

/* CRUD Generator styles */
@import 'vendor/crud-generator/crud-theme.css';
@import 'vendor/crud-generator/crud-components.css';
```

### Thema Wijzigen

**Methode 1: Body class (makkelijkst)**

```html
<!-- layouts/app.blade.php -->
<body class="theme-emerald">
    ...
</body>
```

Beschikbare thema's: `theme-blue`, `theme-emerald`, `theme-teal`, `theme-cyan`, `theme-sky`, `theme-violet`, `theme-purple`, `theme-fuchsia`, `theme-pink`, `theme-rose`, `theme-red`, `theme-orange`, `theme-amber`, `theme-lime`, `theme-green`, `theme-slate`, `theme-zinc`

**Methode 2: CSS variabelen aanpassen**

```css
/* resources/css/app.css - na de imports */

:root {
    /* Pas de primaire kleur aan */
    --crud-primary-500: #8b5cf6;  /* violet */
    --crud-primary-600: #7c3aed;
    --crud-primary-700: #6d28d9;
    
    /* Of gebruik je eigen merkkleur */
    --crud-primary-600: #your-brand-color;
}
```

**Methode 3: Volledig custom thema**

```css
/* resources/css/my-theme.css */

.theme-custom {
    --crud-primary-50:  #fef2f2;
    --crud-primary-100: #fee2e2;
    --crud-primary-200: #fecaca;
    --crud-primary-300: #fca5a5;
    --crud-primary-400: #f87171;
    --crud-primary-500: #ef4444;
    --crud-primary-600: #dc2626;  /* Hoofdkleur */
    --crud-primary-700: #b91c1c;  /* Hover */
    --crud-primary-800: #991b1b;
    --crud-primary-900: #7f1d1d;
}
```

### Beschikbare CSS Classes

```css
/* Buttons */
.crud-btn-primary      /* Primaire button */
.crud-btn-secondary    /* Secundaire button */
.crud-btn-danger       /* Delete button */
.crud-btn-danger-outline /* Delete outline button */

/* Links */
.crud-link-primary     /* Primaire link */
.crud-link-info        /* Info/view link */
.crud-link-warning     /* Edit link */
.crud-link-danger      /* Delete link */

/* Alerts */
.crud-alert-success    /* Success melding */
.crud-alert-danger     /* Error melding */
.crud-alert-warning    /* Warning melding */

/* Forms */
.crud-input           /* Text input */
.crud-textarea        /* Textarea */
.crud-select          /* Select dropdown */
.crud-checkbox        /* Checkbox */
.crud-label           /* Form label */
.crud-label-error     /* Error message */

/* Badges */
.crud-badge-primary   /* Primary badge */
.crud-badge-success   /* Success badge */
.crud-badge-danger    /* Danger badge */
.crud-badge-warning   /* Warning badge */

/* Tables */
.crud-table           /* Table */
.crud-table-th        /* Table header */
.crud-table-td        /* Table cell */
.crud-table-row       /* Table row */
```

### Voorbeeld: Verschillende Kleuren per Sectie

```html
<!-- Admin sectie met paars thema -->
<div class="theme-purple">
    @include('admin.users.index')
</div>

<!-- Blog sectie met groen thema -->
<div class="theme-emerald">
    @include('blog.posts.index')
</div>
```

## 📦 Installatie

```bash
composer require mosweed/laravel-crud-generator --dev
```

Publiceer de configuratie (optioneel):

```bash
php artisan vendor:publish --tag=crud-generator-config
```

Publiceer de stubs voor aanpassing (optioneel):

```bash
php artisan vendor:publish --tag=crud-generator-stubs
```

## 🛠️ Gebruik

### Basis Commando

```bash
php artisan make:crud Post
```

### Met Velden

```bash
php artisan make:crud Post --fields="title:string,body:text,status:enum:draft:published,is_featured:boolean:nullable"
```

### Met Relaties

```bash
php artisan make:crud Post --fields="title:string,body:text" --relations="belongsTo:User:author_id,hasMany:Comment,belongsToMany:Tag"
```

### Interactieve Modus

```bash
php artisan make:crud Post --interactive
```

Dit opent een interactieve wizard waarin je:
1. Velden kunt definiëren met types en modifiers
2. Relaties kunt toevoegen
3. Kunt kiezen welke bestanden gegenereerd worden

### API Only Modus

```bash
php artisan make:crud Post --api
```

Genereert alleen een API controller zonder views.

### Bestaande Bestanden Overschrijven

```bash
php artisan make:crud Post --force
```

### Bulk Generatie met YAML

Maak een YAML configuratiebestand:

```yaml
# crud-config.yaml
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
      body: text
      status:
        type: enum
        modifiers: [draft, published]
    relations:
      - type: belongsTo
        model: User
      - type: belongsTo
        model: Category
```

Voer uit:

```bash
php artisan make:crud-yaml crud-config.yaml
```

## 📋 Veld Types

| Type | Migration | Faker |
|------|-----------|-------|
| `string` | `$table->string()` | `fake()->sentence()` |
| `text` | `$table->text()` | `fake()->paragraphs()` |
| `integer` | `$table->integer()` | `fake()->numberBetween()` |
| `bigInteger` | `$table->bigInteger()` | `fake()->numberBetween()` |
| `float` | `$table->float()` | `fake()->randomFloat()` |
| `decimal` | `$table->decimal()` | `fake()->randomFloat()` |
| `boolean` | `$table->boolean()` | `fake()->boolean()` |
| `date` | `$table->date()` | `fake()->date()` |
| `datetime` | `$table->dateTime()` | `fake()->dateTime()` |
| `time` | `$table->time()` | `fake()->time()` |
| `json` | `$table->json()` | `json_encode([])` |
| `enum` | `$table->enum()` | `fake()->randomElement()` |

## 🔧 Modifiers

| Modifier | Beschrijving |
|----------|--------------|
| `nullable` | Veld mag leeg zijn |
| `unique` | Waarde moet uniek zijn |
| `index` | Database index toevoegen |
| `unsigned` | Alleen positieve getallen |
| `default:value` | Standaardwaarde instellen |

### Voorbeeld met Modifiers

```bash
--fields="email:string:unique,bio:text:nullable,age:integer:unsigned,status:enum:active:inactive:default:active"
```

## 🔗 Relatie Types

### belongsTo (N:1)

```bash
--relations="belongsTo:User:author_id"
```

Genereert:
- Foreign key column in migration
- `belongsTo` methode in model
- Factory met gerelateerd model

### hasMany (1:N)

```bash
--relations="hasMany:Comment"
```

### hasOne (1:1)

```bash
--relations="hasOne:Profile"
```

### belongsToMany (N:N)

```bash
--relations="belongsToMany:Tag"
```

Genereert automatisch een pivot table migration.

### morphTo / morphMany (Polymorphic)

```bash
--relations="morphMany:Comment"
```

## ⚙️ Configuratie

Na het publiceren van de config (`config/crud-generator.php`):

```php
return [
    // Namespace voor gegenereerde bestanden
    'namespace' => 'App',

    // Paden
    'paths' => [
        'model' => app_path('Models'),
        'controller' => app_path('Http/Controllers'),
        // ...
    ],

    // CSS framework: 'tailwind', 'bootstrap', of 'none'
    'css_framework' => 'tailwind',

    // Alleen API genereren (geen views)
    'api_only' => false,

    // Soft deletes standaard inschakelen
    'soft_deletes' => true,

    // Wat te genereren
    'generate' => [
        'model' => true,
        'controller' => true,
        'migration' => true,
        'seeder' => true,
        'factory' => true,
        'request' => true,
        'resource' => true,
        'views' => true,
        'routes' => true,
    ],

    // Paginatie
    'pagination' => [
        'per_page' => 15,
    ],
];
```

## 🎨 Stubs Aanpassen

Na het publiceren van de stubs kun je ze vinden in:

```
resources/stubs/vendor/crud-generator/
├── controller.stub
├── controller.api.stub
├── factory.stub
├── migration.stub
├── migration.pivot.stub
├── model.stub
├── request.stub
├── resource.stub
├── seeder.stub
└── views/
    ├── index.stub
    ├── create.stub
    ├── edit.stub
    ├── show.stub
    └── form.stub
```

## 📁 Gegenereerde Bestanden

Na `php artisan make:crud Post`:

```
app/
├── Http/
│   ├── Controllers/
│   │   └── PostController.php
│   ├── Requests/
│   │   ├── StorePostRequest.php
│   │   └── UpdatePostRequest.php
│   └── Resources/
│       └── PostResource.php
└── Models/
    └── Post.php

database/
├── factories/
│   └── PostFactory.php
├── migrations/
│   └── 2024_01_01_000000_create_posts_table.php
└── seeders/
    └── PostSeeder.php

resources/views/posts/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
└── _form.blade.php

routes/web.php (routes toegevoegd)
```

## 🚀 Na Generatie

1. **Controleer de migration:**
   ```bash
   cat database/migrations/*_create_posts_table.php
   ```

2. **Voer de migration uit:**
   ```bash
   php artisan migrate
   ```

3. **Seed de database:**
   ```bash
   php artisan db:seed --class=PostSeeder
   ```

4. **Bekijk je CRUD:**
   Open `http://yourapp.test/posts` in je browser.

## 🧪 Testen

```bash
composer test
```

## 📝 Voorbeeld Workflow

```bash
# 1. Maak een blog systeem
php artisan make:crud Category --fields="name:string,slug:string:unique" --relations="hasMany:Post"
php artisan make:crud Post --fields="title:string,slug:string:unique,content:text,status:enum:draft:published" --relations="belongsTo:Category,belongsTo:User:author_id,hasMany:Comment"
php artisan make:crud Comment --fields="body:text,is_approved:boolean" --relations="belongsTo:Post,belongsTo:User"

# 2. Migreer
php artisan migrate

# 3. Seed
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=PostSeeder
php artisan db:seed --class=CommentSeeder

# 4. Klaar!
```

## 🤝 Bijdragen

Bijdragen zijn welkom! Open een issue of pull request.

## 📄 Licentie

MIT License. Zie [LICENSE](LICENSE) voor details.

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📝 Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## 🔒 Security

If you discover any security related issues, please email info@mosweed.com instead of using the issue tracker.

## 👏 Credits

- [Mosweed](https://github.com/mosweed)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
