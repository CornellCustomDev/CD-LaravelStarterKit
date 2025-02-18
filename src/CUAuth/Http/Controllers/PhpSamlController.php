<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhpSamlController
{
    public function samlLogin(Request $request)
    {
        $redirectUri = $request->query('redirect_uri', '/');

        // If already logged in, return to the originally intended URL
        if (SamlIdentityManager::getIdentity()) {
            return redirect()->to($redirectUri);
        }

        // Redirect to the SSO URL
        $ssoUrl = SamlIdentityManager::getSsoUrl($redirectUri);

        return redirect($ssoUrl);
    }

    public function samlLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to the originally intended URL
        $returnUrl = $request->query('return', '/');

        return redirect()->to($returnUrl);
    }

    public function samlACS(Request $request)
    {
        try {
            SamlIdentityManager::storeIdentity();
        } catch (Exception $e) {
            return response($e->getMessage(), 403);
        }

        // Redirect to the originally intended URL
        $returnUrl = $request->input('RelayState', '/');

        return redirect()->to($returnUrl);
    }

    public function samlMetadata(Request $request)
    {
        $metadata = SamlIdentityManager::getMetadata();

        return response($metadata)->withHeaders([
            'Content-Type' => 'text/xml',
        ]);
    }
}
