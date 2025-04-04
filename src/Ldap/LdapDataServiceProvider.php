<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use Illuminate\Support\ServiceProvider;

class LdapDataServiceProvider extends ServiceProvider
{
    const INSTALL_CONFIG_TAG = 'starterkit-ldap-config';

    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__.'/../../config/ldap.php',
            key: 'ldap',
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/ldap.php' => config_path('ldap.php'),
            ], StarterKitServiceProvider::PACKAGE_NAME.':'.self::INSTALL_CONFIG_TAG);
        }
    }
}
