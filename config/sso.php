<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Single Sign-On (SSO) system using OpenID Connect.
    | These settings are used to authenticate users with the internal SSO
    | provider and retrieve their permissions.
    |
    */

    'client_id' => env('SSO_CLIENT_ID'),

    'client_secret' => env('SSO_CLIENT_SECRET'),

    'issuer_url' => env('SSO_ISSUER_URL'),

    'redirect_uri' => env('SSO_REDIRECT_URI'),

    'logout_redirect_uri' => env('SSO_LOGOUT_REDIRECT_URI'),

    /*
    |--------------------------------------------------------------------------
    | OpenID Connect Scopes
    |--------------------------------------------------------------------------
    |
    | The scopes to request during authentication. The 'permissions' scope
    | is custom and provides structured permission data in the ID token.
    |
    */

    'scopes' => ['openid', 'profile', 'email', 'permissions'],

    /*
    |--------------------------------------------------------------------------
    | Validation Settings
    |--------------------------------------------------------------------------
    |
    | Settings for token validation and security.
    |
    */

    'verify_ssl' => env('SSO_VERIFY_SSL', true),

    'timeout' => env('SSO_TIMEOUT', 30),

];