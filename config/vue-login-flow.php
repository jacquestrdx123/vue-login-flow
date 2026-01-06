<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Class
    |--------------------------------------------------------------------------
    |
    | The Eloquent model class that will be used for authentication.
    |
    */
    'model' => env('VUE_LOGIN_FLOW_MODEL', 'App\Models\User'),

    /*
    |--------------------------------------------------------------------------
    | Guard Name
    |--------------------------------------------------------------------------
    |
    | The name of the authentication guard.
    |
    */
    'guard_name' => env('VUE_LOGIN_FLOW_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | URL Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for login routes (e.g., '/admin' for /admin/login).
    |
    */
    'url_prefix' => env('VUE_LOGIN_FLOW_URL_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The route prefix (without leading slash) for route groups.
    |
    */
    'route_prefix' => env('VUE_LOGIN_FLOW_ROUTE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Create Login Page
    |--------------------------------------------------------------------------
    |
    | Whether to create the login page Vue component.
    |
    */
    'create_login_page' => env('VUE_LOGIN_FLOW_CREATE_LOGIN_PAGE', true),

    /*
    |--------------------------------------------------------------------------
    | Create HandleInertiaRequest Middleware
    |--------------------------------------------------------------------------
    |
    | Whether to create a separate HandleInertiaRequest middleware for this guard.
    | This allows you to have multiple Inertia share instances.
    |
    */
    'create_middleware' => env('VUE_LOGIN_FLOW_CREATE_MIDDLEWARE', false),

    /*
    |--------------------------------------------------------------------------
    | Auth Type
    |--------------------------------------------------------------------------
    |
    | The authentication type: 'session', 'sanctum', or 'both'.
    |
    */
    'auth_type' => env('VUE_LOGIN_FLOW_AUTH_TYPE', 'session'),
];

