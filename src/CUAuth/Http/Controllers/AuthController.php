<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    public function shibbolethLogin(Request $request)
    {
        $redirectUri = $request->query('redirect_uri', '/');

        if (ShibIdentity::getRemoteUserId($request)) {
            // Already logged in so redirect to the originally intended URL
            return redirect()->to($redirectUri);
        }

        return redirect(config('cu-auth.shibboleth_login_url').'?target='.urlencode($redirectUri));
    }

    public function shibbolethLogout(Request $request)
    {
        // If the user is logged in, log them out
        if (auth()->check()) {
            auth()->logout();
        }

        // Redirect to the Shibboleth logout URL
        $returnUrl = urlencode($request->query('return', '/'));
        return redirect(config('cu-auth.shibboleth_logout_url').'?return='.$returnUrl);
    }
}
