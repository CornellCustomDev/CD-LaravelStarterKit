<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\AppTesters;
use CornellCustomDev\LaravelStarterKit\Tests\Feature\FeatureTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AppTestersTest extends FeatureTestCase
{
    public function testHandleInProduction()
    {
        auth()->login($this->getTestUser());

        Config::set('app.env', 'local');
        $response = (new AppTesters)->handle(new Request, fn () => response('OK'));
        $this->assertTrue($response->isForbidden());

        Config::set('app.env', 'production');
        $response = (new AppTesters)->handle(new Request, fn () => response('OK'));
        $this->assertTrue($response->isOk());
    }

    public function testHandleWithAuthorizedUser()
    {
        Config::set('app.env', 'local');
        Config::set('cu-auth.user_lookup_field', 'id');
        Config::set('cu-auth.app_testers', ['test-user']);
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn((object) ['id' => 'test-user']);

        $response = (new AppTesters)->handle(new Request, fn () => response('OK'));

        $this->assertTrue($response->isOk());
    }

    public function testHandleWithUnauthorizedUser()
    {
        Config::set('app.env', 'local');
        Config::set('cu-auth.user_lookup_field', 'id');
        Config::set('cu-auth.app_testers', ['test-user']);
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn((object) ['id' => 'not-test-user']);

        $response = (new AppTesters)->handle(new Request, fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }
}
