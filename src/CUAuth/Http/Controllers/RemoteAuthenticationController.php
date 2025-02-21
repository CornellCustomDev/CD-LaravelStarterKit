<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class RemoteAuthenticationController extends BaseController
{
    protected IdentityManager $identityManager;

    public function __construct(IdentityManager $identityManager)
    {
        $this->identityManager = $identityManager;
    }

    public function login(Request $request)
    {
        $redirectUrl = $request->query('redirect_url', '/');

        if ($this->identityManager->hasIdentity()) {
            // Already logged in so redirect to the originally intended URL
            return redirect()->to($redirectUrl);
        }

        // Use the Shibboleth login URL
        $ssoUrl = $this->identityManager->getSsoUrl($redirectUrl);

        return redirect($ssoUrl);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $returnUrl = $request->query('return', '/');

        $sloUrl = $this->identityManager->getSloUrl($returnUrl);

        return redirect($sloUrl);
    }
}
