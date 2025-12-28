<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Namespace
    |--------------------------------------------------------------------------
    */
    'namespace' => 'App',

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'model' => app_path('Models'),
        'controller' => app_path('Http/Controllers'),
        'request' => app_path('Http/Requests'),
        'resource' => app_path('Http/Resources'),
        'migration' => database_path('migrations'),
        'seeder' => database_path('seeders'),
        'factory' => database_path('factories'),
        'view' => resource_path('views'),
        'route' => base_path('routes'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CSS Framework
    |--------------------------------------------------------------------------
    |
    | Het CSS framework voor de views: 'tailwind' of 'bootstrap'
    | Bij tailwind worden CSS variabelen gebruikt voor kleuren.
    | Publiceer de CSS bestanden met: php artisan vendor:publish --tag=crud-generator-assets
    |
    */
    'css_framework' => 'tailwind',

    /*
    |--------------------------------------------------------------------------
    | API Only
    |--------------------------------------------------------------------------
    */
    'api_only' => false,

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    */
    'soft_deletes' => true,

    /*
    |--------------------------------------------------------------------------
    | Timestamps
    |--------------------------------------------------------------------------
    */
    'timestamps' => true,

    /*
    |--------------------------------------------------------------------------
    | Generate Resources
    |--------------------------------------------------------------------------
    */
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
        'policy' => false,
        'test' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Options
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => '',
        'middleware' => ['web'],
        'api_middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Types Mapping
    |--------------------------------------------------------------------------
    */
    'field_types' => [
        'string' => 'text',
        'text' => 'textarea',
        'integer' => 'number',
        'bigInteger' => 'number',
        'float' => 'number',
        'decimal' => 'number',
        'boolean' => 'checkbox',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'time' => 'time',
        'email' => 'email',
        'password' => 'password',
        'json' => 'textarea',
        'enum' => 'select',
    ],
];
