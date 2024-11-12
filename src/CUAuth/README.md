# CUAuth

Middleware for authorizing Laravel users.

## ApacheShib

Use with Apache mod_shib to authorize users.

### Usage

```php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::group(['middleware' => [ApacheShib::class]], function () {
    // Protected routes here
    Route::get('profile', [UserController::class, 'show']);
});
```

_Note that the route `cu-auth.shibboleth-login` (`/shibboleth-login`) is utilized for handling the login process. This architecture supports sites that do not authenticate all pages and allows Laravel to manage authorization._

#### Simple authentication

With just the ApacheShib middleware, the site will require users to be authenticated via Shibboleth with the default 
server variable "REMOTE_USER". If all pages should be protected and all users should be allowed, this is all that is 
needed.

#### User authorization

When a user is authenticated via Shibboleth, the `CUAuthenticated` event is fired so that the site can authorize the 
user. The site must [register a listener](https://laravel.com/docs/11.x/events#registering-events-and-listeners) for
the `CUAuthenticated` event. This listener should take the `$userId` from the event to look up the user in the database 
and log them in or create as user as needed.

> [AuthorizeUser](Listeners/AuthorizeUser.php) is provided as a starting point for handling the CUAuthenticated event.
> It is simplistic and should be replaced with a site-specific implementation in the site code base. It demonstrates 
> retrieving user data from [ShibIdentity](DataObjects/ShibIdentity.php) and creating a user if they do not exist. 

### Configuration and Testing

For mod_shib, the default is to look for the server variable "REMOTE_USER". This variable name is configured in 
[config/cu-auth.php](../../config/cu-auth.php) and can be overridden by setting "APACHE_SHIB_USER_VARIABLE" in the `.env` .

For local testing, setting "REMOTE_USER" in the project `.env` file will override the server variable. Note that
`APP_ENV` must be set to "local" for this to work.

```env
APP_ENV=local
REMOTE_USER=abc123
```


## AppTesters

Limits non-production access to users in the `APP_TESTERS` environment variable.

### Usage

```php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::group(['middleware' => [ApacheShib::class, AppTesters::class], function () {
    Route::get('profile', [UserController::class, 'show']);
});
```

On non-production sites, the [AppTesters](Middleware/AppTesters.php) middleware checks the "APP_TESTERS" environment variable for a comma-separated list of users. If a user is logged in and not in the list, the middleware will return an HTTP_FORBIDDEN response.

The field used for looking up users is `netid` by default. It is configurable in the [config/cu-auth.php](../../config/cu-auth.php) file as `user_lookup_field`.



## LocalLogin
_Note: For testing purposes, the environment variable "ALLOW_LOCAL_LOGIN" can be set to true to bypass the middleware for a currently authenticated user._
