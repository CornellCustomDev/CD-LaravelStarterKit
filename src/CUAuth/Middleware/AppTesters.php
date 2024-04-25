<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppTesters
{
    public function handle(Request $request, Closure $next): Response
    {
        // Anyone can use production
        if (config('app.env') == 'production') {
            return $next($request);
        }

        // If there is a logged-in user, check against app_testers
        if (auth()->check()) {
            $userLookupField = config('cu-auth.user_lookup_field');
            $userId = auth()->user()->$userLookupField;
            if (in_array($userId, config('cu-auth.app_testers'))) {
                return $next($request);
            }
        }

        return response('Forbidden', Response::HTTP_FORBIDDEN);
    }
}
