<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix & Domain
    |--------------------------------------------------------------------------
    |
    | Filament is the primary admin interface at `/admin`, `/app`, `/staff`,
    | and `/reception`. To prevent collision, this package routes to `/ops-console`.
    |
    */
    'prefix' => env('ADMIN_PANEL_PREFIX', 'ops-console'),

    'domain' => env('ADMIN_PANEL_DOMAIN', null),

    'middleware' => [
        'web',
    ],

    'auth' => [
        'guard' => env('ADMIN_PANEL_GUARD', 'web'),
        'user_model' => env('ADMIN_PANEL_USER_MODEL', 'App\\Models\\User'),
        'enable_registration' => false,
        'enable_password_reset' => true,
        'enable_2fa' => false,
        'enable_passkeys' => false,
    ],

    'theme' => [
        'brand_name' => 'Ops Console',
        'brand_logo' => null,
        'brand_logo_dark' => null,
        'default_mode' => 'system',
        'default_palette' => 'indigo',
        'sidebar_variant' => 'inset',
        'density' => 'comfortable',
        'radius' => 'md',
        'font_family' => 'Inter, system-ui, sans-serif',
    ],

    'layout' => [
        'max_width' => 'full',
        'sidebar_collapsed' => false,
        'breadcrumbs' => true,
        'sticky_header' => true,
    ],

    'features' => [
        'rbac' => false,
        'media_library' => true,
        'audit_log' => true,
        'activity_feed' => true,
        'notifications' => true,
        'global_search' => true,
        'system_health' => true,
        'settings' => true,
        'api' => false,
        'tenancy' => false,
        'ai' => false,
    ],

    'assets' => [
        'publish_path' => 'vendor/admin-panel',
        'serve_fallback' => true,
    ],
];
