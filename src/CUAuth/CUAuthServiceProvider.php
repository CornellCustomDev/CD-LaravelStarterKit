<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\ShibIdentityManager;
use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use Illuminate\Support\ServiceProvider;

class CUAuthServiceProvider extends ServiceProvider
{
    const INSTALL_CONFIG_TAG = 'cu-auth-config';

    const INSTALL_PHP_SAML_TAG = 'php-saml-toolkit-config';

    const APACHE_SHIB = 'apache-shib';

    const PHP_SAML = 'php-saml';

    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__.'/../../config/cu-auth.php',
            key: 'cu-auth',
        );
        $this->mergeConfigFrom(
            path: __DIR__.'/../../config/php-saml-toolkit.php',
            key: 'php-saml-toolkit',
        );
        $this->app->singleton(
            abstract: IdentityManager::class,
            concrete: match (config('cu-auth.identity_manager')) {
                self::APACHE_SHIB => fn () => new ShibIdentityManager,
                self::PHP_SAML => fn () => new SamlIdentityManager,
            },
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/cu-auth.php' => config_path('cu-auth.php'),
            ], StarterKitServiceProvider::PACKAGE_NAME.':'.self::INSTALL_CONFIG_TAG);
            $this->publishes([
                __DIR__.'/../../config/php-saml-toolkit.php' => config_path('php-saml-toolkit.php'),
            ], StarterKitServiceProvider::PACKAGE_NAME.':'.self::INSTALL_PHP_SAML_TAG);
        }
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
