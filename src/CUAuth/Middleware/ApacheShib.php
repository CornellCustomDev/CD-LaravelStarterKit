<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApacheShib
{
    public function handle(Request $request, Closure $next): Response
    {
        // If local login is allowed and someone is authenticated, let them through.
        if (config('cu-auth.allow_local_login') && auth()->check()) {
            return $next($request);
        }

        // If this is a local development environment, allow the local override.
        $remote_user_override = app()->isLocal() ? config('cu-auth.remote_user_override') : null;

        // Apache mod_shib populates the remote user variable if someone is logged in.
        $userId = $request->server(config('cu-auth.apache_shib_user_variable'), $remote_user_override);

        // If no remote user is found, return a 403.
        if (empty($userId)) {
            // @TODO: Do we need an unauthenticated event to match CUAuthenticated?
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        $userLookupField = config('cu-auth.user_lookup_field');

        // If we are not using a user lookup field, the user is allowed.
        if (empty($userLookupField)) {
            return $next($request);
        }

        event(new CUAuthenticated($userId, $userLookupField));

        // If the authenticated user is not logged in, return a 403.
        if (! auth()->check()) {
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
