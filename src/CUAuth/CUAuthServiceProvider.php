<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth;

use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class CUAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__.'/../../config/cu-auth.php',
            key: 'cu-auth',
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                paths: [
                    __DIR__.'/../../config/cu-auth.php' => config_path('cu-auth.php'),
                ],
                groups: [
                    StarterKitServiceProvider::PACKAGE_NAME.':config',
                    'cu-auth-config',
                ],
            );
        }

        Gate::define('use-site', function ($user) {
            // Anyone can use production
            if (config('app.env') == 'production') {
                return true;
            }

            // Only configured APP_TESTERS can use non-production
            if (in_array($user->netid, config('cu-auth.app_testers'))) {
                return true;
            }

            return false;
        });
    }
}
