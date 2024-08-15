<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ApacheShib Configuration
    |--------------------------------------------------------------------------
    |
    | ApacheShib retrieves the user's identifier from a server variable
    | populated by the Apache shibboleth module (mod_shib).
    |
    | The default variable is "REMOTE USER", but this may be different
    | on some servers, e.g., REDIRECT_REMOTE_USER, depending on how PHP is
    | installed.
    |
    | For local development without shibboleth, you can add
    | REMOTE_USER=<netid> to your project .env file to log in as that user.
    |
    */
    'apache_shib_user_variable' => env('APACHE_SHIB_USER_VARIABLE', 'REMOTE_USER'),
    'remote_user_override' => env('REMOTE_USER'),

    /*
    |--------------------------------------------------------------------------
    | AppTesters Configuration
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of users to allow in development environments.
    |
    */
    'app_testers' => Str::of(env('APP_TESTERS', ''))->split('/[\s,]+/')->filter()->all(),

    /*
    |--------------------------------------------------------------------------
    | User Lookup Field
    |--------------------------------------------------------------------------
    |
    | What field on the user model should be used to look up the user when
    | firing CUAuthenticated events or comparing with APP_TESTERS?
    |
    */
    'user_lookup_field' => env('USER_LOOKUP_FIELD', 'netid'),

    /*
    |--------------------------------------------------------------------------
    | Allow Local Login
    |--------------------------------------------------------------------------
    |
    | Allow Laravel password-based login? Typically, this would only be used
    | for local or automated testing.
    |
    */
    'allow_local_login' => boolval(env('ALLOW_LOCAL_LOGIN', false)),
];
