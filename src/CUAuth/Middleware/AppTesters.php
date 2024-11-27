<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Middleware;

use Closure;
use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
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

        if (auth()->check()) {
            $appTestersField = config('cu-auth.app_testers_field');
            $tester = auth()->user()->$appTestersField ?? '';
        } else {
            // @TODO Should this be calling a generalized method, not Shibboleth specific?
            $tester = ShibIdentity::getRemoteUser($request);
        }

        if ($this->app_testers->contains($tester)) {
            return $next($request);
        }

        if (app()->runningInConsole()) {
            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }
        abort(403);
    }
}
