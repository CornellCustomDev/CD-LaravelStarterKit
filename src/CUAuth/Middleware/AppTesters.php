<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AppTesters
{
    private Collection $app_testers;

    public function __construct()
    {
        $this->app_testers = Str::of(config('cu-auth.app_testers'))
            ->split('/[\s,]+/')
            ->filter();
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Anyone can use production
        if (config('app.env') == 'production') {
            return $next($request);
        }

        // If no app_testers are defined, anyone can use
        if ($this->app_testers->isEmpty()) {
            return $next($request);
        }

        // If there is no logged-in user, we cannot check against app_testers
        if (! auth()->check()) {
            return $next($request);
        }

        $userLookupField = config('cu-auth.user_lookup_field');
        $userId = auth()->user()->$userLookupField ?? '';
        if ($this->app_testers->contains($userId)) {
            return $next($request);
        }

        if (app()->runningInConsole()) {
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }
        abort(403);
    }
}
