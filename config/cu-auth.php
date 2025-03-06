<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identity Manager
    |--------------------------------------------------------------------------
    |
    | The identity manager to use for user authentication. Options are:
    | - apache-shib: Apache mod_shib
    | - php-saml: OneLogin SAML PHP Toolkit
    |
    */
    'identity_manager' => env('CU_AUTH_IDENTITY_MANGER', 'apache-shib'),

    /*
    |--------------------------------------------------------------------------
    | Require Local User
    |--------------------------------------------------------------------------
    |
    | Require a local user account in the application for the authenticated
    | remote user. This setting controls the CUAuthenticated event to handle
    | user login.
    |
    */
    'require_local_user' => env('REQUIRE_LOCAL_USER', false),

    /*
    |--------------------------------------------------------------------------
    | ApacheShib Configuration
    |--------------------------------------------------------------------------
    |
    | ApacheShib retrieves user data from server variables populated by the
    | Apache shibboleth module (mod_shib).
    |
    | The default user variable is "REMOTE_USER", but this may differ depending
    | on how mod_shib is configured.
    |
    | For local development without shibboleth, you can add
    | REMOTE_USER=<netid> to your project .env file to log in as that user.
    |
    */
    'apache_shib_user_variable' => env('APACHE_SHIB_USER_VARIABLE', 'REMOTE_USER'),
    'remote_user_override' => env('REMOTE_USER'),

    /*
    |--------------------------------------------------------------------------
    | PHP-SAML Configuration
    |--------------------------------------------------------------------------
    |
    | Path for storing SAML certificates outside of version control.
    |
    | (Note: Configuration for the OneLogin SAML PHP Toolkit is primarily
    | in config/php-saml-toolkit.php.)
    |
    | The default path of storage/app/keys is ignored by git in a standard
    | Laravel installation, so typically this does not need to be changed.
    |
    */
    'cert-path' => storage_path(env('SAML_CERT_PATH', 'app/keys')),

    /*
    |--------------------------------------------------------------------------
    | AppTesters Configuration
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of users to allow in development environments.
    | APP_TESTERS_FIELD is the field on the user model to compare against.
    |
    */
    'app_testers' => env('APP_TESTERS', ''),
    'app_testers_field' => env('APP_TESTERS_FIELD', 'netid'),

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
