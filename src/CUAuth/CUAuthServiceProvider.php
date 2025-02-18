<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth;

use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use Illuminate\Support\ServiceProvider;

class CUAuthServiceProvider extends ServiceProvider
{
    const INSTALL_CONFIG_TAG = 'cu-auth-config';

    const INSTALL_PHP_SAML_TAG = 'php-saml-config';

    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__.'/../../config/cu-auth.php',
            key: 'cu-auth',
        );
        $this->mergeConfigFrom(
            path: __DIR__.'/../../config/php-saml.php',
            key: 'php-saml',
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/cu-auth.php' => config_path('cu-auth.php'),
            ], StarterKitServiceProvider::PACKAGE_NAME.':'.self::INSTALL_CONFIG_TAG);
            $this->publishes([
                __DIR__.'/../../config/php-saml.php' => config_path('php-saml.php'),
            ], StarterKitServiceProvider::PACKAGE_NAME.':'.self::INSTALL_PHP_SAML_TAG);
        }
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
