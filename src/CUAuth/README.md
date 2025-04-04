# CUAuth

Middleware for authorizing Laravel users.

- [ApacheShib](#apacheshib) - Apache mod_shib integration
- [AppTesters](#apptesters) - Limit access to users in the `APP_TESTERS` environment variable
- [Local Login](#local-login) - Allow Laravel users to log in with a local username and password


## ApacheShib

Use with Apache mod_shib to authorize users.

### Usage

```php
// File: routes/web.php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::group(['middleware' => [ApacheShib::class]], function () {
    // Protected routes here
    Route::get('profile', [UserController::class, 'show']);
});
```

> _See also: [shibboleth configuration](SHIBBOLETH.md)._

#### Simple authentication

If all pages should be protected and all authenticated users should be authorized, only the ApacheShib middleware is
needed.

#### User authorization

If the site should only allow users who are in the database, set `REQUIRE_LOCAL_USER=true` in the `.env` file.

```dotenv
# File: .env
REQUIRE_LOCAL_USER=true
```

Requiring a local user triggers the `CUAuthenticated` event when a user is authenticated via Shibboleth. The site must
[register a listener](https://laravel.com/docs/11.x/events#registering-events-and-listeners) for
the `CUAuthenticated` event. This listener should look up the user in the database and log them in or create a user
as needed.

> [AuthorizeUser](Listeners/AuthorizeUser.php) is provided as a starting point for handling the CUAuthenticated event.
> It is simplistic and should be replaced with a site-specific implementation in the site code base. It demonstrates 
> retrieving user data from [ShibIdentity](DataObjects/ShibIdentity.php) and creating a user if they do not exist. 

### Configuration

See [config/cu-auth.php](../../config/cu-auth.php) for configuration options, all of which can be set with environment variables.

To modify the default configuration, publish the configuration file:

```bash
php artisan vendor:publish --tag=starterkit:cu-auth-config
```

### Local testing

For local testing where mod_shib is not available, the `REMOTE_USER` environment variable can be set to simulate
Shibboleth authentication. Note that `APP_ENV` must be set to "local" for this to work.

```dotenv
# File: .env
APP_ENV=local
REMOTE_USER=abc123
```

### Notes

The route `cu-auth.shibboleth-login` (`/shibboleth-login`) is utilized for handling the login process. This
architecture supports sites that do not authenticate all pages and allows Laravel to manage authorization.

Similarly, the route `cu-auth.shibboleth-logout` (`/shibboleth-logout`) is utilized for handling the logout process.


## AppTesters

Limits non-production access to users in the `APP_TESTERS` environment variable.

### Usage

```php
// File: routes/web.php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::group(['middleware' => [ApacheShib::class, AppTesters::class], function () {
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

