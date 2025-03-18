# CUAuth

Middleware for authorizing Laravel users.

- [CUAuth SSO](#cuauth) - Single sign-on and authorization middleware 
  - SAML PHP Toolkit integration
  - Apache mod_shib integration
- [AppTesters](#apptesters) - Limit access to users in the `APP_TESTERS` environment variable
- [Local Login](#local-login) - Allow Laravel users to log in with a local username and password

## Use Cases

- **Single Sign-On**: Protect routes with SSO (mod_shib or PHP SAML)
  - Optionally log in SSO users to app user accounts
- **AppTesters**: Limit access to non-production users


## Single Sign-On

### Usage

```php
// File: routes/web.php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\CUAuth;

Route::group(['middleware' => [CUAuth::class]], function () {
    // Protected routes here
    Route::get('profile', [UserController::class, 'show']);
});
```

```dotenv
# File: .env
# apache-shib (default) | php-saml
CU_AUTH_IDENTITY_MANGER=apache-shib
```

See [Authorization](#identity-and-authorization) for details on how to log in remote users for local authorization.

> _See also: [shibboleth configuration](SHIBBOLETH.md)._

---

### Routing

Any pages protected by middleware are automatically redirected to SSO. To directly trigger log in or log out, use the following routes (parameters are optional and will default to `'/'`):
- Login: `route('cu-auth.sso-login', ['redirect_url' => '/home'])` 
- Logout: `route('cu-auth.sso-logout', ['return' => '/'])`

### Certs and Metadata (php-saml)

For using the PHP SAML Toolkit, the SAML keys and certs can be generated with the following command, or as an option from the starter kit installer:

```bash
  php artisan cu-auth:generate-keys
```

The SAML metadata can be retrieved at `https://<site-url>/sso/metadata`.

The default location for the SAML keys and certs is in `storage/app/keys`. This location is configurable in the `config/cu-auth.php` file or by setting the `SAML_CERT_PATH` in `.env`.

---

### Local Testing (apache-shib)
For local testing where mod_shib is not available, the `REMOTE_USER` environment variable can be set to simulate
Shibboleth authentication. Note that `APP_ENV` must be set to "local" for this to work and the config cache must be cleared when `REMOTE_USER` is changed.

```dotenv
# File: .env
APP_ENV=local
REMOTE_USER=abc123
```

## Identity and Authorization

Once authenticated via SSO, the user's identity is available via the `IdentityManager`, which can be accessed via the app container.

```php
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;

$remoteIdentity = app(IdentityManager::class)->getIdentity();
$netid = $remoteIdentity->id(); // NetID | CWID

// If provided with SSO attributes:
$email = $remoteIdentity->email(); // Primary email (i.e. netid@cornell.edu)
$name = $remoteIdentity->name(); // Display name
```

### User authorization

If the site should manage authorization for users in the application, set `config('cu-auth.require_local_user')` to true:

```php
# File: config/cu-auth.php
'require_local_user' => env('REQUIRE_LOCAL_USER', true),
```

Requiring a local user triggers the `CUAuthenticated` event when a user is authenticated via single sign-on. The site must
[register a listener](https://laravel.com/docs/11.x/events#registering-events-and-listeners) for
the `CUAuthenticated` event. This listener should look up the user in the database and log them in or create a user
as needed.

> [AuthorizeUser](Listeners/AuthorizeUser.php) is provided as a starting point for handling the CUAuthenticated event.
> It is simplistic and should be replaced with a site-specific implementation in the site code base. It demonstrates 
> retrieving user data via the [IdentityManager](Managers/IdentityManager.php) and creating a user if they do not exist. 


## Configuration

See [config/cu-auth.php](../../config/cu-auth.php) for configuration options, all of which can be set with environment variables.

To modify the default configuration, publish the configuration file:

```bash
  php artisan vendor:publish --tag=starterkit:cu-auth-config
```

To modify the PHP SAML Toolkit configuration, publish the configuration file:

```bash
  php artisan vendor:publish --tag=starterkit:php-saml-toolkit-config
```


## AppTesters

Limits non-production access to users in the `APP_TESTERS` environment variable.

### Usage

```php
// File: routes/web.php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\CUAuth;

Route::group(['middleware' => [CUAuth::class, AppTesters::class], function () {
    Route::get('profile', [UserController::class, 'show']);
});
```

```dotenv
# File: .env
APP_TESTERS=abc123,def456
```

On non-production sites, the [AppTesters](Middleware/AppTesters.php) middleware checks the "APP_TESTERS" environment variable for a comma-separated list of users. If a user is logged in and not in the list, the middleware will return an HTTP_FORBIDDEN response.

The field used for looking up users is `netid` by default. It is configured in [config/cu-auth.php](../../config/cu-auth.php) file as `app_testers_field`.


## Local Login
For testing purposes, the environment variable "ALLOW_LOCAL_LOGIN" can be set to true to bypass the middleware for a currently authenticated user.
```dotenv
# File: .env
ALLOW_LOCAL_LOGIN=true

