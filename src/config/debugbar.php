<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Debugbar Settings
    |--------------------------------------------------------------------------
    |
    | Debugbar is enabled by default when APP_DEBUG is true.
    | You can override this value to explicitly enable or disable it.
    |
    */

    'enabled' => env('DEBUGBAR_ENABLED', env('APP_DEBUG', false)),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | DebugBar stores data about previous requests for this browser session.
    |
    */

    'storage' => [
        'enabled' => true,
        'driver' => 'file',
        'path' => storage_path('debugbar'),
        'max_files' => 20, // Maximum number of stored requests
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Collectors
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific collectors
    |
    */

    'collectors' => [
        'time' => true,
        'memory' => true,
        'request' => true,
        'database' => true,
        'views' => true,
        'route' => true,
        'logs' => true,
        'session' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Injection Settings
    |--------------------------------------------------------------------------
    |
    | Configure where and how the debugbar is injected into your pages.
    |
    */

    'inject' => true,

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URI prefix for accessing debugbar assets and data.
    |
    */

    'route_prefix' => '_debugbar',

    /*
    |--------------------------------------------------------------------------
    | Capture Ajax Requests
    |--------------------------------------------------------------------------
    |
    | Should the debugbar capture AJAX requests?
    |
    */

    'capture_ajax' => true,

    /*
    |--------------------------------------------------------------------------
    | Display Options
    |--------------------------------------------------------------------------
    |
    | Configure the appearance of the debugbar.
    |
    */

    'options' => [
        'position' => 'bottom', // bottom, top
        'theme' => 'auto', // auto, light, dark
        'max_queries' => 100,
        'max_logs' => 100,
    ],
];