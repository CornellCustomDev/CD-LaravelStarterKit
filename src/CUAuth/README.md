# CUAuth

Middleware for authorizing Laravel users.

Multiple middleware implementations are available:
- [ApacheShib](./Middleware/ApacheShib.php): Authorize users based on the apache mod_shib server variable.
- [AppTesters](./Middleware/AppTesters.php): Limits non-production access to users in the `APP_TESTERS` environment variable.

_Note: For testing purposes, the environment variable "ALLOW_LOCAL_LOGIN" can be set to true to bypass the middleware for a currently authenticated user._

## ApacheShib
The [ApacheShib](./Middleware/ApacheShib.php) middleware looks for the server variable "REMOTE_USER" and fires a `CUAuthenticated` event if it is set. (
See [config/cu-auth.php](../../config/cu-auth.php) `apache_shib_user_variable` if you need to modify the server variable name.) If the REMOTE_USER is not set or if the CUAuthenticated
event handling does not result in a user being logged in, the middleware will return an HTTP_FORBIDDEN response.

The site must [register a listener](https://laravel.com/docs/10.x/events#registering-event-subscribers) for
the `CUAuthenticated` event. This listener can use the `$userId` from the event to look up the user in the database and
log them in or create as user as needed.

> [AuthorizeUser](./Listeners/AuthorizeUser.php) is provided as a starting point for handling the CUAuthenticated event.
> It is simplistic and should be replaced with a site-specific implementation in the site code base.

Example route usage:

```php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::get('profile', [UserController::class, 'show'])->middleware(ApacheShib::class);
```

## AppTesters
On non-production sites, the [AppTesters](./Middleware/AppTesters.php) middleware checks the "APP_TESTERS" environment variable for a comma-separated list of users. If the current user is not logged in and in the list, the middleware will return an HTTP_FORBIDDEN response.

The field used for looking up users is `netid` by default. It is configurable in the [config/cu-auth.php](../../config/cu-auth.php) file as `user_lookup_field`.

Example route usage integrated with ApacheShib:

```php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::group(['middleware' => [AppTesters::class]) ], function () {
    Route::get('profile', [UserController::class, 'show'])->middleware(ApacheShib::class);
});
```

