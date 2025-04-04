<?php

return [
    /*
    |----------------------------------------------------------------------
    | LDAP Credentials
    |----------------------------------------------------------------------
    |
    | The credentials used to connect to the LDAP server, which must be set
    | in the .env file.
    |
    | Note that LDAP_USER must be a fully-qualified DN, so we append the
    | default Cornell base DN for authorized users.
    |
    */
    'user' => env('LDAP_USER') ? env('LDAP_USER').',ou=Directory Administrators,o=Cornell University,c=US' : '',
    'pass' => env('LDAP_PASS') ?? '',

    /*
    |----------------------------------------------------------------------
    | LDAP Server
    |----------------------------------------------------------------------
    |
    | The LDAP server to connect to and base_dn, defaulting to the Cornell
    | LDAP server and base DN.
    |
    */
    'server' => env('LDAP_SERVER', 'ldaps://query.directory.cornell.edu'),
    'base_dn' => env('LDAP_BASE_DN', 'ou=People,o=Cornell University,c=US'),

    /*
    |----------------------------------------------------------------------
    | LDAP Cache
    |----------------------------------------------------------------------
    |
    | The number of seconds to cache LDAP queries. This is useful for
    | performance, but be careful not to cache too long, as LDAP data
    | can change frequently.
    |
    */
    'cache_seconds' => env('LDAP_CACHE_SECONDS', 300),
];
