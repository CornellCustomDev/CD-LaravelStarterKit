<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature;

use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\CUAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class CUAuthTest extends FeatureTestCase
{
    public function testCanFailAuthenticatingLocalUser()
    {
        config(['cu-auth.allow_local_login' => true]);
        $middleware = new CUAuth();

        $response = $middleware->handle(new Request(), fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }

    /**
     * Local user is already authenticated.
     */
    public function testAuthenticatesLocalUser()
    {
        config(['cu-auth.allow_local_login' => true]);
        $this->actingAs($this->getTestUser());
        $middleware = new CUAuth();

        $response = $middleware->handle(new Request(), fn () => response('OK'));

        $this->assertTrue($response->isOk());
    }

    /**
     * Remote user is not authenticated.
     */
    public function testCanFailAuthenticatingRemoteUser()
    {
        $middleware = new CUAuth();

        $response = $middleware->handle(new Request(), fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }

    /**
     * Remote user is authenticated but does not have an account.
     */
    public function testCallsCUAuthenticatedWithUserId()
    {
        Event::fake([
            CUAuthenticated::class,
        ]);
        config(['cu-auth.remote_user_variable' => 'REMOTE_USER']);
        $request = new Request();
        $middleware = new CUAuth();

        $request->server->set('REMOTE_USER', 'new-user');
        $middleware->handle($request, fn () => response('OK'));

        Event::assertDispatched(CUAuthenticated::class, fn ($event) => $event->userId === 'new-user');
    }

    /**
     * Routes for local auth testing.
     */
    protected function usesLocalAuthRoute($router): void
    {
        $router->get('/test/require-auth', fn () => 'OK')->name('test.require-auth')
            ->middleware('auth');
        // Laravel requires a route named "login" in local login workflow.
        $router->get('/test/login', fn () => 'OK')->name('login');
    }

    /**
     * Routes for CUAuth testing.
     */
    protected function usesCuAuthRoute($router): void
    {
        $router->get('/test/require-cu-auth', fn () => 'OK')->name('test.require-cu-auth')
            ->middleware(CUAuth::class);
    }

    /**
     * @define-route usesLocalAuthRoute
     * @define-route usesCuAuthRoute
     */
    public function testRoutesAreProtected()
    {
        $this->get(route('test'))->assertOk();
        $this->get(route('test.require-auth'))->assertRedirect('/test/login');
        $this->get(route('test.require-cu-auth'))->assertForbidden();
    }

    /** @define-route usesLocalAuthRoute */
    public function testRouteIsProtectedForLocalUser()
    {
        config(['cu-auth.allow_local_login' => true]);
        $this->actingAs($this->getTestUser());

        $this->get(route('test.require-auth'))->assertOk();
    }

    /** @define-route usesCuAuthRoute */
    public function testRouteIsProtectedForRemoteUser()
    {
        Event::fake([
            CUAuthenticated::class,
        ]);
        config(['cu-auth.remote_user_variable' => 'REMOTE_USER']);
        $this->withServerVariables(['REMOTE_USER' => 'new-user']);

        $this->get(route('test.require-cu-auth'));

        Event::assertDispatched(CUAuthenticated::class, fn ($event) => $event->userId === 'new-user');
    }

    /**
     * Example implementation of CUAuthenticated listener.
     *
     * '$authorized' would be defined in the listener, not passed as a parameter.
     */
    public function useCUAuthenticatedListener(bool $authorized = true): void
    {
        Event::listen(CUAuthenticated::class, function ($event) use ($authorized) {
            if ($authorized) {
                auth()->login($this->getTestUser($event->userId));
            } elseif (auth()->check()) {
                auth()->logout();
            }
        });
    }

    public function testLogsInAuthorizedUser(): void
    {
        $this->useCUAuthenticatedListener();

        config(['cu-auth.remote_user_variable' => 'REMOTE_USER']);
        $request = new Request();
        $middleware = new CUAuth();

        $request->server->set('REMOTE_USER', 'auth-user');
        $response = $middleware->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isOk());
    }

    public function testLogsOutUnauthorizedUser(): void
    {
        $this->useCUAuthenticatedListener(false);

        config(['cu-auth.remote_user_variable' => 'REMOTE_USER']);
        $user = $this->getTestUser();
        $request = new Request();
        $middleware = new CUAuth();

        $this->actingAs($user);
        $request->server->set('REMOTE_USER', $user->name);
        $response = $middleware->handle($request, fn () => response('OK'));

        $this->assertTrue($response->isForbidden());
    }
}
