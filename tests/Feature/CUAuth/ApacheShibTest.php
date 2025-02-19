<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers\ApacheShibController;
use CornellCustomDev\LaravelStarterKit\CUAuth\Listeners\AuthorizeUser;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\ShibIdentityManager;
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
        Event::listen(CUAuthenticated::class, function (CUAuthenticated $event) use ($authorized) {
            if ($authorized) {
                auth()->login($this->getTestUser($event->remoteUser));
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
        config(['cu-auth.apache_shib_user_variable' => 'REMOTE_USER_TEST']);

        $request = new Request;
        $request->setLaravelSession(app('session.store'));
        if ($remote_user !== null) {
            $request->server->set('REMOTE_USER_TEST', $remote_user);
        }

        return $request;
    }

    /**
     * Remote user is NOT authenticated by Apache mod_shib.
     */
    public function testRedirectsUnauthenticatedRemoteUser()
    {
        $this->addCUAuthenticatedListener();
        $request = $this->getApacheAuthRequest();

        $response = (new ApacheShibController)->shibbolethLogin($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString(config('cu-auth.shibboleth_login_url'), $response->getTargetUrl());
    }

    /**
     * Remote user is authenticated by Apache mod_shib.
     */
    public function testAuthenticatesRemoteUser()
    {
        $this->addCUAuthenticatedListener();
        $request = $this->getApacheAuthRequest('new-user');
        $request->query->set('redirect_uri', '/test');

        $response = (new ApacheShibController)->shibbolethLogin($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('/test', $response->getTargetUrl());
    }

    public function testLogsOutRemoteUser()
    {
        $user = $this->getTestUser();
        auth()->login($user);
        $request = $this->getApacheAuthRequest($user->email);
        $request->query->set('return', '/test');

        $response = (new ApacheShibController)->shibbolethLogout($request);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString(config('cu-auth.shibboleth_logout_url'), $response->getTargetUrl());
        $this->assertStringContainsString(urlencode('/test'), $response->getTargetUrl());
    }

    /**
     * Remote user is authenticated but not authorized.
     */
    public function testCanFailAuthorizing()
    {
        config(['cu-auth.require_local_user' => true]);
        $this->addCUAuthenticatedListener(authorized: false);
        $request = $this->getApacheAuthRequest('new-user');

        $response = (new ApacheShib)->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }

    /**
     * Local user is NOT authenticated.
     */
    public function testCanFailAuthenticatingLocalUser()
    {
        config(['cu-auth.allow_local_login' => true]);

        $response = (new ApacheShib)->handle(new Request, fn () => response('OK'));

        $this->assertTrue($response->isRedirect());
    }

    /**
     * Local user is authenticated.
     */
    public function testAuthenticatesLocalUser()
    {
        config(['cu-auth.allow_local_login' => true]);
        auth()->login($this->getTestUser());

        $response = (new ApacheShib)->handle(new Request, fn () => response('OK'));

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
        // Fake shibboleth login url route.
        $router->get(config('cu-auth.shibboleth_login_url'), fn () => 'ShibUrl')->name('cu-auth.shibboleth-login');
    }

    /**
     * @define-route usesAuthRoutes
     */
    public function testRoutesAreProtected()
    {
        $this->get(route('test'))->assertOk();
        $this->get(route('test.require-auth'))->assertRedirect('/test/login');
        $this->get(route('test.require-cu-auth'))->assertRedirectContains(route('cu-auth.shibboleth-login'));
        $this->followingRedirects()->get(route('test.require-cu-auth'))->assertSee('ShibUrl');
    }

    /** @define-route usesAuthRoutes */
    public function testRouteIsProtectedForRemoteUser()
    {
        $this->addCUAuthenticatedListener();
        config(['cu-auth.apache_shib_user_variable' => 'REMOTE_USER_TEST']);

        // No user is authenticated.
        $this->followingRedirects()->get(route('test.require-cu-auth'))->assertSee('ShibUrl');

        // Remote user is authenticated.
        $this->withServerVariables(['REMOTE_USER_TEST' => 'new-user']);
        $this->get(route('test.require-cu-auth'))->assertOk();
    }

    /** @define-route usesAuthRoutes */
    public function testRouteIsProtectedForProductionRemoteUser()
    {
        $this->addCUAuthenticatedListener();
        config(['cu-auth.remote_user_override' => 'new-user']);

        // Override does not work in production environment.
        $this->app->detectEnvironment(fn () => 'production');
        $this->followingRedirects()->get(route('test.require-cu-auth'))->assertSee('ShibUrl');

        // Override works in local environment.
        $this->app->detectEnvironment(fn () => 'local');
        $this->get(route('test.require-cu-auth'))->assertOk();
    }

    /** @define-route usesAuthRoutes */
    public function testRouteIsProtectedForLocalUser()
    {
        config(['cu-auth.apache_shib_user_variable' => 'REMOTE_USER_TEST']);
        config(['cu-auth.allow_local_login' => true]);

        // No user is authenticated.
        $this->followingRedirects()->get(route('test.require-cu-auth'))->assertSee('ShibUrl');

        // Local user is authenticated.
        $this->actingAs($this->getTestUser());
        $this->get(route('test.require-cu-auth'))->assertOk();
    }

    public function testRouteIsProtectedWithoutUserLookup()
    {
        config(['cu-auth.remote_user_override' => 'new-user']);

        // Require an authenticated user.
        $this->addCUAuthenticatedListener(authorized: false);
        $request = $this->getApacheAuthRequest('new-user');

        $response = (new ApacheShib)->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isOk());
    }

    public function testShibIdentity()
    {
        $shib = ShibIdentityManager::fromServerVars([
            'Shib_Identity_Provider' => 'https://shibidp-test.cit.cornell.edu/idp/shibboleth',
            'uid' => 'netid',
            'mail' => 'netid@cornell.edu',
        ]);

        $this->assertTrue($shib->isCornellIdP());
        $this->assertFalse($shib->isWeillIdP());
        $this->assertEquals('netid', $shib->uniqueUid());
        $this->assertEquals('netid@cornell.edu', $shib->email());
    }

    public function testShibWeillIdentity()
    {
        $shib = ShibIdentityManager::fromServerVars([
            'Shib_Identity_Provider' => 'https://login-test.weill.cornell.edu/idp',
            'uid' => 'cwid',
            'mail' => 'cwid@med.cornell.edu',
        ]);

        $this->assertFalse($shib->isCornellIdP());
        $this->assertTrue($shib->isWeillIdP());
        $this->assertEquals('cwid_w', $shib->uniqueUid());
        $this->assertEquals('cwid@med.cornell.edu', $shib->email());
    }

    public function testShibNames()
    {
        $shib = ShibIdentityManager::fromServerVars([
            'displayName' => 'Test User',
        ]);
        $this->assertEquals('Test User', $shib->name());

        $shib = ShibIdentityManager::fromServerVars([
            'cn' => 'Test User',
        ]);
        $this->assertEquals('Test User', $shib->name());

        $shib = ShibIdentityManager::fromServerVars([
            'givenName' => 'Test',
            'sn' => 'User',
        ]);
        $this->assertEquals('Test User', $shib->name());
    }

    public function testAuthorizeUser()
    {
        $serverVars = [
            'Shib_Identity_Provider' => 'https://shibidp-test.cit.cornell.edu/idp/shibboleth',
            'uid' => 'netid',
            'displayName' => 'Test User',
            'mail' => 'netid@cornell.edu',
        ];
        $event = new CUAuthenticated('netid@cornell.edu');
        $listener = new AuthorizeUser;
        $listener->handle($event, $serverVars);

        $this->assertTrue(auth()->check());
        $this->assertEquals('Test User', auth()->user()->name);
        $this->assertEquals('netid@cornell.edu', auth()->user()->email);
    }
}
