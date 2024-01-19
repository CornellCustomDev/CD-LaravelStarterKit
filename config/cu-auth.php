<?php

return [
    /*
     * CUAuth retrieves the user's identifier from an environment variable
     * populated by the Apache shibboleth module (mod_shib).
     *
     * The default variable is "REMOTE USER", but this may be different
     * on some servers, e.g., REDIRECT_REMOTE_USER, depending on how PHP is
     * installed.
     *
     * For local development without shibboleth, you can add
     * REMOTE_USER=<netid> to your project .env file to log in as that user.
     */
    'remote_user_variable' => env('REMOTE_USER_VARIABLE', 'REMOTE_USER'),

    /*
     * What field on the user model should be used to look up the user?
     */
    'remote_user_lookup_field' => env('REMOTE_USER_LOOKUP_FIELD', 'netid'),

    /*
     * Allow Laravel password-based login?
     */
    'allow_local_login' => (bool) env('ALLOW_LOCAL_LOGIN', false),

    /*
     * Development testing users, comma-separated.
     */
    'app_testers' => explode(',', env('APP_TESTERS', '')),
];
