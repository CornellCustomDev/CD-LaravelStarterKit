<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;
use CornellCustomDev\LaravelStarterKit\Tests\Feature\FeatureTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class ApacheShibTest extends FeatureTestCase
{
    /**
     * Simple implementation of CUAuthenticated listener.
     */
    private function addCUAuthenticatedListener(bool $authorized = true): void
    {
        Event::listen(CUAuthenticated::class, function ($event) use ($authorized) {
            if ($authorized) {
                auth()->login($this->getTestUser($event->userId));
            } elseif (auth()->check()) {
                auth()->logout();
            }
        });
    }

    /**
     * Get a request with Apache mod_shib authentication set (or null).
     */
    private function getApacheAuthRequest($remote_user = null): Request
    {
        config(['cu-auth.apache_shib_user_variable' => 'REMOTE_USER']);

        $request = new Request();
        if ($remote_user !== null) {
            $request->server->set('REMOTE_USER', $remote_user);
        }

        return $request;
    }

    /**
     * Remote user is NOT authenticated by Apache mod_shib.
     */
    public function testCanFailAuthenticatingRemoteUser()
    {
        $this->addCUAuthenticatedListener();
        $request = $this->getApacheAuthRequest();

        $response = (new ApacheShib())->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }

    /**
     * Remote user is authenticated by Apache mod_shib.
     */
    public function testAuthenticatesRemoteUser()
    {
        $this->addCUAuthenticatedListener();
        $request = $this->getApacheAuthRequest('new-user');

        $response = (new ApacheShib())->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isOk());
    }

    /**
     * Remote user is authenticated but not authorized.
     */
    public function testCanFailAuthorizing()
    {
        $this->addCUAuthenticatedListener(authorized: false);
        $request = $this->getApacheAuthRequest('new-user');

        $response = (new ApacheShib())->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }

    /**
     * Local user is NOT authenticated.
     */
    public function testCanFailAuthenticatingLocalUser()
    {
        config(['cu-auth.allow_local_login' => true]);

        $response = (new ApacheShib())->handle(new Request(), fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }

    /**
     * Local user is authenticated.
     */
    public function testAuthenticatesLocalUser()
    {
        config(['cu-auth.allow_local_login' => true]);
        auth()->login($this->getTestUser());

        $response = (new ApacheShib())->handle(new Request(), fn () => response('OK'));

        $this->assertTrue($response->isOk());
    }

    /**
     * Routes for auth testing.
     */
    protected function usesAuthRoutes($router): void
    {
        $router->get('/test/require-cu-auth', fn () => 'OK')->name('test.require-cu-auth')
            ->middleware(ApacheShib::class);
        $router->get('/test/require-auth', fn () => 'OK')->name('test.require-auth')
            ->middleware('auth');
        // Laravel requires a route named "login" in auth login workflow.
        $router->get('/test/login', fn () => 'OK')->name('login');
    }

    /**
     * @define-route usesAuthRoutes
     */
    public function testRoutesAreProtected()
    {
        $this->get(route('test'))->assertOk();
        $this->get(route('test.require-auth'))->assertRedirect('/test/login');
        $this->get(route('test.require-cu-auth'))->assertForbidden();
    }

    /** @define-route usesAuthRoutes */
    public function testRouteIsProtectedForRemoteUser()
    {
        $this->addCUAuthenticatedListener();
        config(['cu-auth.apache_shib_user_variable' => 'REMOTE_USER']);

        // No user is authenticated.
        $this->get(route('test.require-cu-auth'))->assertForbidden();

        // Remote user is authenticated.
        $this->withServerVariables(['REMOTE_USER' => 'new-user']);
        $this->get(route('test.require-cu-auth'))->assertOk();
    }

    /** @define-route usesAuthRoutes */
    public function testRouteIsProtectedForLocalUser()
    {
        config(['cu-auth.apache_shib_user_variable' => 'REMOTE_USER']);
        config(['cu-auth.allow_local_login' => true]);

        // No user is authenticated.
        $this->get(route('test.require-cu-auth'))->assertForbidden();

        // Local user is authenticated.
        $this->actingAs($this->getTestUser());
        $this->get(route('test.require-cu-auth'))->assertOk();
    }
}
