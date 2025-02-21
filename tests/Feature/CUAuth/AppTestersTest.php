<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\ShibIdentityManager;
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
        Config::set('cu-auth.app_testers', 'test-user');
        $response = (new AppTesters(new ShibIdentityManager))->handle(new Request, fn () => response('OK'));
        $this->assertTrue($response->isForbidden());

        Config::set('app.env', 'production');
        $response = (new AppTesters(new ShibIdentityManager))->handle(new Request, fn () => response('OK'));
        $this->assertTrue($response->isOk());
    }

    public function testHandleWithAuthorizedUser()
    {
        Config::set('app.env', 'local');
        Config::set('cu-auth.app_testers_field', 'id');
        Config::set('cu-auth.app_testers', 'a-user, test-user');
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn((object) ['id' => 'test-user']);

        $response = (new AppTesters(new ShibIdentityManager))->handle(new Request, fn () => response('OK'));

        $this->assertTrue($response->isOk());
    }

    public function testHandleWithUnauthorizedUser()
    {
        Config::set('app.env', 'local');
        Config::set('cu-auth.app_testers_field', 'id');
        Config::set('cu-auth.app_testers', 'test-user');
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn((object) ['id' => 'not-test-user']);

        $response = (new AppTesters(new ShibIdentityManager))->handle(new Request, fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }

    public function testHandleWithNoUser()
    {
        Config::set('app.env', 'local');
        Config::set('cu-auth.app_testers_field', 'id');
        Config::set('cu-auth.app_testers', 'test-user');
        Auth::shouldReceive('check')->andReturn(false);

        $response = (new AppTesters(new ShibIdentityManager))->handle(new Request, fn () => response('OK'));

        // If there is no logged-in user, they should not be able to use the app.
        $this->assertTrue($response->isForbidden());
    }

    public function testHandleWithNoAppTesters()
    {
        Config::set('app.env', 'local');
        Config::set('cu-auth.app_testers_field', 'id');
        Config::set('cu-auth.app_testers', '');
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn((object) ['id' => 'test-user']);

        $response = (new AppTesters(new ShibIdentityManager))->handle(new Request, fn () => response('OK'));

        // If there are no app testers, anyone can use the app.
        $this->assertTrue($response->isOk());
    }
}
