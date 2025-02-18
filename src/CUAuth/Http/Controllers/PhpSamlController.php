<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\SamlIdentity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OneLogin\Saml2\AuthnRequest;
use OneLogin\Saml2\Settings;

class PhpSamlController
{
    public function samlLogin(Request $request)
    {
        $redirectUri = $request->query('redirect_uri', '/');

        // If already logged in, return to the originally intended URL
        if (SamlIdentity::getRemoteUser()) {
            return redirect()->to($redirectUri);
        }

        $settings = new Settings(config('php-saml'));
        $authRequest = new AuthnRequest($settings);
        $ssoUrl = url(
            path: $settings->getIdPData()['singleSignOnService']['url'],
            parameters: [
                'SAMLRequest' => $authRequest->getRequest(),
                'RelayState' => $redirectUri,
            ]
        );

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
        $auth = new \OneLogin\Saml2\Auth(settings: config('php-saml'));
        $auth->processResponse();
        $errors = $auth->getErrors();
        if (! empty($errors)) {
            throw new Exception('SAML Response Errors: '.implode(', ', $errors));
        }
        if (! $auth->isAuthenticated()) {
            throw new Exception('SAML Response not authenticated');
        }

        // Store the user in the session
        $samlIdentity = SamlIdentity::fromAuth($auth);
        if ($samlIdentity) {
            $request->session()->put('samlIdentity', $samlIdentity);
        }

        // Redirect to the originally intended URL
        $returnUrl = $request->input('RelayState', '/');

        return redirect()->to($returnUrl);
    }

    public function samlMetadata(Request $request)
    {
        $settings = new Settings(config('php-saml'), true);
        $metadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);
        if (! empty($errors)) {
            throw new Exception('Invalid SP metadata: '.implode(', ', $errors));
        }

        return response($metadata)->withHeaders([
            'Content-Type' => 'text/xml',
        ]);
    }
}
