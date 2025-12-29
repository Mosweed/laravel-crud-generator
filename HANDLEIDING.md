# 📚 Laravel CRUD Generator - Stap-voor-Stap Handleiding

## Inhoudsopgave

1. [Installatie](#1-installatie)
2. [Basis Gebruik](#2-basis-gebruik)
3. [Velden Definiëren](#3-velden-definiëren)
4. [Relaties Toevoegen](#4-relaties-toevoegen)
5. [Views & Kleuren Aanpassen](#5-views--kleuren-aanpassen)
6. [YAML Configuratie (Meerdere Models)](#6-yaml-configuratie-meerdere-models)
7. [Na het Genereren](#7-na-het-genereren)
8. [Voorbeelden](#8-voorbeelden)
9. [Veelgestelde Vragen](#9-veelgestelde-vragen)

---

## 1. Installatie

### Stap 1.1: Package Installeren

```bash
# Via Composer
composer require mosweed/laravel-crud-generator --dev
```

### Stap 1.2: Config Publiceren (optioneel)

```bash
# Publiceer de configuratie
php artisan vendor:publish --tag=crud-generator-config
```

Dit maakt `config/crud-generator.php` aan waar je standaard instellingen kunt aanpassen.

### Stap 1.3: CSS Bestanden Publiceren (voor kleuren)

```bash
# Publiceer de CSS bestanden
php artisan vendor:publish --tag=crud-generator-assets
```

### Stap 1.4: CSS Importeren in je Project

Open `resources/css/app.css` en voeg toe:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* CRUD Generator styles - voeg deze regels toe */
@import 'vendor/crud-generator/crud-theme.css';
@import 'vendor/crud-generator/crud-components.css';
```

### Stap 1.5: CSS Compileren

```bash
npm run build
# of voor development:
npm run dev
```

---

## 2. Basis Gebruik

### Stap 2.1: Simpele CRUD Genereren

```bash
php artisan make:crud Post
```

Dit genereert:
- ✅ Migration
- ✅ Model
- ✅ Controller
- ✅ Form Requests (Store & Update)
- ✅ API Resource
- ✅ Factory
- ✅ Seeder
- ✅ Views (index, create, edit, show, _form)
- ✅ Routes

### Stap 2.2: CRUD met Velden Genereren

```bash
php artisan make:crud Post --fields="title:string,body:text,is_published:boolean"
```

### Stap 2.3: Interactieve Modus

```bash
php artisan make:crud Post --interactive
```

Je wordt dan stap voor stap door het proces geleid.

---

## 3. Velden Definiëren

### Veld Syntax

```
veldnaam:type:modifier1:modifier2
```

### Beschikbare Veld Types

| Type | Beschrijving | Voorbeeld |
|------|-------------|-----------|
| `string` | Korte tekst (max 255) | `title:string` |
| `text` | Lange tekst | `body:text` |
| `integer` | Geheel getal | `views:integer` |
| `bigInteger` | Groot geheel getal | `total:bigInteger` |
| `float` | Decimaal getal | `rating:float` |
| `decimal` | Precisie decimaal | `price:decimal` |
| `boolean` | Ja/Nee | `is_active:boolean` |
| `date` | Datum | `birth_date:date` |
| `datetime` | Datum + tijd | `published_at:datetime` |
| `time` | Alleen tijd | `start_time:time` |
| `json` | JSON data | `metadata:json` |
| `enum` | Keuze uit opties | `status:enum:draft:published` |

### Beschikbare Modifiers

| Modifier | Beschrijving | Voorbeeld |
|----------|-------------|-----------|
| `nullable` | Veld mag leeg zijn | `bio:text:nullable` |
| `unique` | Waarde moet uniek zijn | `email:string:unique` |
| `index` | Database index | `slug:string:index` |
| `unsigned` | Alleen positieve getallen | `age:integer:unsigned` |
| `default:waarde` | Standaard waarde | `status:string:default:draft` |

### Voorbeelden

```bash
# Blog post met alle velden
php artisan make:crud Post --fields="title:string,slug:string:unique,body:text,excerpt:text:nullable,is_featured:boolean,published_at:datetime:nullable,views:integer:unsigned:default:0"

# Product met prijs
php artisan make:crud Product --fields="name:string,description:text,price:decimal,stock:integer:unsigned,is_active:boolean"

# User profile
php artisan make:crud Profile --fields="bio:text:nullable,website:string:nullable,avatar:string:nullable,birth_date:date:nullable"

# Order met status enum
php artisan make:crud Order --fields="order_number:string:unique,total:decimal,status:enum:pending:processing:shipped:delivered:cancelled,notes:text:nullable"
```

---

## 4. Relaties Toevoegen

### Relatie Syntax

```
--relations="type:Model:foreign_key"
```

### Beschikbare Relatie Types

| Type | Beschrijving | Voorbeeld |
|------|-------------|-----------|
| `belongsTo` | Behoort tot (N:1) | `belongsTo:User` |
| `hasMany` | Heeft veel (1:N) | `hasMany:Comment` |
| `hasOne` | Heeft één (1:1) | `hasOne:Profile` |
| `belongsToMany` | Veel-op-veel (N:N) | `belongsToMany:Tag` |
| `morphTo` | Polymorf (child) | `morphTo:commentable` |
| `morphMany` | Polymorf (parent) | `morphMany:Comment` |

### Voorbeelden

```bash
# Post behoort tot User, heeft veel Comments
php artisan make:crud Post \
  --fields="title:string,body:text" \
  --relations="belongsTo:User,hasMany:Comment"

# Post met custom foreign key (author_id ipv user_id)
php artisan make:crud Post \
  --fields="title:string,body:text" \
  --relations="belongsTo:User:author_id"

# Post met Tags (veel-op-veel)
php artisan make:crud Post \
  --fields="title:string,body:text" \
  --relations="belongsTo:Category,belongsToMany:Tag"

# Comment behoort tot Post en User
php artisan make:crud Comment \
  --fields="body:text,is_approved:boolean" \
  --relations="belongsTo:Post,belongsTo:User"
```

---

## 5. Views & Kleuren Aanpassen

### Methode 1: Body Class (Makkelijkst)

Open je layout bestand (`resources/views/layouts/app.blade.php`) en voeg een theme class toe:

```html
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="theme-emerald">  <!-- Verander hier het thema -->
    @yield('content')
</body>
</html>
```

### Beschikbare Thema's

| Kleur | Class |
|-------|-------|
| 🔵 Blauw | `theme-blue` |
| 🟢 Emerald | `theme-emerald` |
| 🌊 Teal | `theme-teal` |
| 💎 Cyan | `theme-cyan` |
| ☁️ Sky | `theme-sky` |
| 💜 Violet | `theme-violet` |
| 🟣 Paars | `theme-purple` |
| 💗 Fuchsia | `theme-fuchsia` |
| 🌸 Pink | `theme-pink` |
| 🌹 Rose | `theme-rose` |
| 🔴 Rood | `theme-red` |
| 🟠 Oranje | `theme-orange` |
| 🟡 Amber | `theme-amber` |
| 💚 Lime | `theme-lime` |
| 🟢 Groen | `theme-green` |
| 🔘 Slate | `theme-slate` |
| ⚫ Zinc | `theme-zinc` |

### Methode 2: CSS Variabelen Aanpassen

Open `resources/css/vendor/crud-generator/crud-theme.css` en pas de variabelen aan:

```css
:root {
    /* Verander deze naar je merk kleuren */
    --crud-primary-600: #7c3aed;  /* Hoofdkleur */
    --crud-primary-700: #6d28d9;  /* Hover kleur */
    --crud-primary-500: #8b5cf6;  /* Focus ring */
}
```

### Methode 3: Eigen Thema Maken

Voeg toe aan je `resources/css/app.css`:

```css
.theme-mijnbedrijf {
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

Gebruik dan: `<body class="theme-mijnbedrijf">`

---

## 6. YAML Configuratie (Meerdere Models)

Voor grote projecten kun je meerdere models in één keer genereren.

### Stap 6.1: Maak een YAML bestand

Maak `crud-config.yaml` in je project root:

```yaml
models:
  # Categorieën
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

  # Blog Posts
  Post:
    fields:
      title: string
      slug:
        type: string
        modifiers: [unique]
      body: text
      excerpt:
        type: text
        modifiers: [nullable]
      is_published:
        type: boolean
      published_at:
        type: datetime
        modifiers: [nullable]
      views:
        type: integer
        modifiers: [unsigned, "default:0"]
    relations:
      - type: belongsTo
        model: Category
      - type: belongsTo
        model: User
        foreign_key: author_id
      - type: hasMany
        model: Comment
      - type: belongsToMany
        model: Tag

  # Comments
  Comment:
    fields:
      body: text
      is_approved:
        type: boolean
    relations:
      - type: belongsTo
        model: Post
      - type: belongsTo
        model: User

  # Tags
  Tag:
    fields:
      name: string
      slug:
        type: string
        modifiers: [unique]
    relations:
      - type: belongsToMany
        model: Post
```

### Stap 6.2: Genereer alle Models

```bash
php artisan make:crud-yaml crud-config.yaml
```

### Stap 6.3: Met --force om te overschrijven

```bash
php artisan make:crud-yaml crud-config.yaml --force
```

---

## 7. Na het Genereren

### Stap 7.1: Bekijk de Gegenereerde Bestanden

Na `php artisan make:crud Post --fields="title:string,body:text"` heb je:

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

routes/web.php  (routes toegevoegd)
```

### Stap 7.2: Controleer de Migration

Open de migration en controleer of alles klopt:

```php
// database/migrations/xxxx_create_posts_table.php

public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('body');
        $table->timestamps();
        $table->softDeletes();
    });
}
```

### Stap 7.3: Draai de Migration

```bash
php artisan migrate
```

### Stap 7.4: Seed de Database (optioneel)

```bash
# Enkele seeder
php artisan db:seed --class=PostSeeder

# Of voeg toe aan DatabaseSeeder.php
$this->call([
    PostSeeder::class,
]);

# Dan: 
php artisan db:seed
```

### Stap 7.5: Bekijk je Routes

```bash
php artisan route:list --name=posts
```

Output:
```
GET|HEAD   posts .............. posts.index › PostController@index
POST       posts .............. posts.store › PostController@store
GET|HEAD   posts/create ....... posts.create › PostController@create
GET|HEAD   posts/{post} ....... posts.show › PostController@show
PUT|PATCH  posts/{post} ....... posts.update › PostController@update
DELETE     posts/{post} ....... posts.destroy › PostController@destroy
GET|HEAD   posts/{post}/edit .. posts.edit › PostController@edit
```

### Stap 7.6: Open in Browser

```bash
php artisan serve
```

Ga naar: `http://localhost:8000/posts`

---

## 8. Voorbeelden

### Voorbeeld 1: Simpele Blog

```bash
# Stap 1: Maak Category
php artisan make:crud Category --fields="name:string,slug:string:unique"

# Stap 2: Maak Post
php artisan make:crud Post \
  --fields="title:string,slug:string:unique,body:text,is_published:boolean" \
  --relations="belongsTo:Category,belongsTo:User:author_id"

# Stap 3: Maak Comment
php artisan make:crud Comment \
  --fields="body:text,is_approved:boolean" \
  --relations="belongsTo:Post,belongsTo:User"

# Stap 4: Migreer
php artisan migrate

# Stap 5: Seed
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=PostSeeder
php artisan db:seed --class=CommentSeeder
```

### Voorbeeld 2: E-commerce Producten

```bash
# Categorie
php artisan make:crud Category \
  --fields="name:string,slug:string:unique,description:text:nullable,image:string:nullable"

# Product
php artisan make:crud Product \
  --fields="name:string,slug:string:unique,description:text,price:decimal,sale_price:decimal:nullable,stock:integer:unsigned:default:0,sku:string:unique,is_active:boolean,is_featured:boolean" \
  --relations="belongsTo:Category,belongsToMany:Tag"

# Product Review
php artisan make:crud Review \
  --fields="rating:integer:unsigned,title:string,body:text,is_approved:boolean" \
  --relations="belongsTo:Product,belongsTo:User"

php artisan migrate
```

### Voorbeeld 3: Taken Systeem

```bash
# Project
php artisan make:crud Project \
  --fields="name:string,description:text:nullable,deadline:date:nullable,status:enum:planning:active:completed:archived" \
  --relations="belongsTo:User,hasMany:Task"

# Task
php artisan make:crud Task \
  --fields="title:string,description:text:nullable,priority:enum:low:medium:high:urgent,status:enum:todo:in_progress:review:done,due_date:date:nullable,completed_at:datetime:nullable" \
  --relations="belongsTo:Project,belongsTo:User:assigned_to"

php artisan migrate
```

### Voorbeeld 4: API Only

```bash
# Genereer alleen API (geen views)
php artisan make:crud Post \
  --fields="title:string,body:text" \
  --relations="belongsTo:User" \
  --api
```

Dit genereert:
- API Controller (met JSON responses)
- API Resource
- Routes in `routes/api.php`
- Geen Blade views

---

## 9. Veelgestelde Vragen

### Q: Hoe overschrijf ik bestaande bestanden?

```bash
php artisan make:crud Post --fields="title:string" --force
```

### Q: Hoe genereer ik alleen bepaalde bestanden?

Pas de config aan in `config/crud-generator.php`:

```php
'generate' => [
    'model' => true,
    'controller' => true,
    'migration' => true,
    'seeder' => false,      // Geen seeder
    'factory' => false,     // Geen factory
    'request' => true,
    'resource' => true,
    'views' => true,
    'routes' => true,
],
```

### Q: Hoe pas ik de gegenereerde code aan?

Publiceer de stubs:

```bash
php artisan vendor:publish --tag=crud-generator-stubs
```

Pas dan de bestanden aan in `resources/stubs/vendor/crud-generator/`.

### Q: Hoe voeg ik middleware toe aan routes?

Pas aan in `config/crud-generator.php`:

```php
'routes' => [
    'middleware' => ['web', 'auth'],  // Voeg auth toe
],
```

### Q: Hoe wijzig ik de paginatie?

```php
// config/crud-generator.php
'pagination' => [
    'per_page' => 25,  // Standaard 15
],
```

### Q: Kan ik de views in een subfolder genereren?

```bash
php artisan make:crud Admin/Post --fields="title:string"
```

Dit maakt views in `resources/views/admin/posts/`.

### Q: Hoe werkt soft deletes?

Standaard aan. Uitzetten in config:

```php
'soft_deletes' => false,
```

### Q: Krijg ik een foutmelding over routes?

Zorg dat je `routes/web.php` en `routes/api.php` bestaan en beginnen met:

```php
<?php

use Illuminate\Support\Facades\Route;
```

---

## 🆘 Hulp Nodig?

- **GitHub Issues**: [github.com/mosweed/laravel-crud-generator/issues](https://github.com/mosweed/laravel-crud-generator/issues)
- **Email**: info@mosweed.com

---

## 📝 Snelle Referentie

```bash
# Basis
php artisan make:crud ModelName

# Met velden
php artisan make:crud ModelName --fields="field:type:modifier"

# Met relaties
php artisan make:crud ModelName --relations="type:RelatedModel"

# Interactief
php artisan make:crud ModelName --interactive

# API only
php artisan make:crud ModelName --api

# Overschrijven
php artisan make:crud ModelName --force

# Vanuit YAML
php artisan make:crud-yaml config.yaml

# Assets publiceren
php artisan vendor:publish --tag=crud-generator-assets
```
