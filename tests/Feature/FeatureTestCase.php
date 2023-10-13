<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature;

use CornellCustomDev\LaravelStarterKit\Tests\TestCase;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Str;

class FeatureTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);

            // Setup queue database connections.
            $config->set('queue.batching.database', 'testbench');
            $config->set('queue.failed.database', 'testbench');
        });
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations(['--database' => 'testbench']);
    }

    protected function defineRoutes($router): void
    {
        $router->get('/test', fn () => 'OK')->name('test');
    }

    protected function getTestUser(
        string $name = 'PHPUnit Test User',
        string $email = 'test@example.com',
    ): User {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Str::random(32);

        return $user;
    }
}
