<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PhpSaml
{
    public function handle(Request $request, Closure $next): Response
    {
        // If local login is allowed and someone is authenticated, let them through.
        if (config('cu-auth.allow_local_login') && auth()->check()) {
            return $next($request);
        }

        // Saml login && acs routes are allowed to pass through.
        if ($request->path() == route('cu-auth.saml-login') || $request->path() == route('cu-auth.saml-acs')) {
            return $next($request);
        }

        // remoteUser will be set for authenticated users.
        $remoteUser = SamlIdentityManager::getIdentity()?->uniqueUid();

        // Unauthenticated get redirected to saml login.
        if (empty($remoteUser)) {
            return redirect()->route('cu-auth.saml-login', [
                'redirect_uri' => $request->fullUrl(),
            ]);
        }

        // If requiring a local user, attempt to log in the user.
        if (config('cu-auth.require_local_user') && ! auth()->check()) {
            event(new CUAuthenticated($remoteUser));

            // If the authenticated user is still not logged in, return a 403.
            if (! auth()->check()) {
                if (app()->runningInConsole()) {
                    return response('Forbidden', Response::HTTP_FORBIDDEN);
                }
                abort(403);
            }
        }

        return $next($request);
    }
}
