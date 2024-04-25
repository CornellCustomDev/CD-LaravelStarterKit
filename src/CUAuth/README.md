# CUAuth

A middleware for authorizing Laravel users based on Apache mod_shib authentication.

The middleware looks for the server variable "REMOTE_USER" and fires a `CUAuthenticated` event if it is set. (
See `.env.example` if you need to modify the server variable.) If the REMOTE_USER is not set or if the CUAuthenticated
event handling does not result in a user being logged in, the middleware will return an HTTP_FORBIDDEN response.

The site must [register a listener](https://laravel.com/docs/10.x/events#registering-event-subscribers) for
the `CUAuthenticated` event. This listener can use the `$userId` from the event to look up the user in the database and
log them in or create as user as needed.

> [AuthorizeUser](./Listeners/AuthorizeUser.php) is provided as a starting point for handling the CUAuthenticated event.
> It is simplistic and should be replaced with a site-specific implementation in the site code base.

Example usage with routes:

```php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::get('profile', [UserController::class, 'show'])->middleware(ApacheShib::class);
```
