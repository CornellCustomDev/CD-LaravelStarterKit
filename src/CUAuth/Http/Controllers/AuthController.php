<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\ShibIdentity;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    public function shibbolethLogin(Request $request)
    {
        $redirectUri = $request->query('redirect_uri', '/');

        if (ShibIdentity::getRemoteUser($request)) {
            // Already logged in so redirect to the originally intended URL
            return redirect()->to($redirectUri);
        }

        // Use the Shibboleth login URL
        return redirect(config('cu-auth.shibboleth_login_url').'?target='.urlencode($redirectUri));
    }

    public function shibbolethLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $returnUrl = $request->query('return', '/');

        if (ShibIdentity::getRemoteUserOverride()) {
            // If using locally configured remote user, there is no Shibboleth logout
            return redirect()->to($returnUrl);
        }

        // Use the Shibboleth logout URL
        return redirect(config('cu-auth.shibboleth_logout_url').'?return='.urlencode($returnUrl));
    }
}
