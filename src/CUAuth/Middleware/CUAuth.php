<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CUAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // If local login is allowed and someone is authenticated, let them through.
        if (config('cu-auth.allow_local_login') && auth()->check()) {
            return $next($request);
        }

        // Apache mod_shib populates the remote user variable if someone is logged in.
        $userId = $request->server(config('cu-auth.remote_user_variable'), config('cu-auth.remote_user'));

        // If no remote user is found, return a 403.
        if (empty($userId)) {
            // @TODO: Do we need an unauthenticated event?
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        // If we have a user who is already logged in, let them through.
        if (auth()->check()) {
            // @TODO: Should this confirm that it's the same user?
            return $next($request);
        }

        // Allow listeners to authorize the user
        event(new CUAuthenticated($userId));

        // If the authenticated user is not logged in, return a 403.
        if (! auth()->check()) {
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
