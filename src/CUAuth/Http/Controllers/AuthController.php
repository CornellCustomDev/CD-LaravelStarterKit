<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends BaseController
{
    public function shibbolethLogin(Request $request)
    {
        $userId = ShibIdentity::getRemoteUserId($request);

        // If no remote user is found, return a 403.
        if (empty($userId)) {
            if (app()->runningInConsole()) {
                return response('Forbidden', Response::HTTP_FORBIDDEN);
            }
            // If the user is logged in, log them out
            if (auth()->check()) {
                auth()->logout();
            }
            abort(403);
        }

        $userLookupField = config('cu-auth.user_lookup_field');

        // If we are using a user lookup field, attempt to log in the user.
        if (! empty($userLookupField)) {
            event(new CUAuthenticated($userId, $userLookupField));

            // If the authenticated user is not logged in, return a 403.
            if (! auth()->check()) {
                if (app()->runningInConsole()) {
                    return response('Forbidden', Response::HTTP_FORBIDDEN);
                }
                abort(403);
            }
        }

        // Successfully logged in so redirect to the originally intended URL
        $redirectUri = $request->query('redirect_uri', '/');

        return redirect()->to($redirectUri);
    }
}
