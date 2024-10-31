<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
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

        if ($request->path() == route('cu-auth.shibboleth-login')) {
            return $next($request);
        }

        // If no remote user is found, authenticate.
        $remoteUserId = ShibIdentity::getRemoteUserId($request);
        if (empty($remoteUserId)) {
            return redirect()->route('cu-auth.shibboleth-login', [
                'redirect_uri' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }
}
