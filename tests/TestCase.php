<?php

namespace CornellCustomDev\LaravelStarterKit\Tests;

use CornellCustomDev\LaravelStarterKit\CUAuth\CUAuthServiceProvider;
use CornellCustomDev\LaravelStarterKit\Ldap\LdapDataServiceProvider;
use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app): array
    {
        return [
            CUAuthServiceProvider::class,
            StarterKitServiceProvider::class,
            LdapDataServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }

    public function fixture(string $name, bool $json = false): array|string
    {
        $contents = file_get_contents(
            filename: __DIR__."/Fixtures/$name",
        );

        if (! $contents) {
            throw new InvalidArgumentException(
                message: "Cannot find fixture: tests/Fixtures/$name",
            );
        }

        return $json ? json_decode(
            json: $contents,
            associative: true,
        ) : $contents;
    }
}
