<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Http\Controllers;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\IdentityManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    public function __construct(
        protected IdentityManager $identityManager
    ) {}

    public function login(Request $request)
    {
        $redirectUrl = $request->query('redirect_url', '/');

        $remoteIdentity = $this->identityManager->getIdentity();
        if ($remoteIdentity) {
            return redirect()->to($redirectUrl);
        }

        // Use the SSO login URL
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

    public function acs(Request $request)
    {
        try {
            $this->identityManager->storeIdentity();
        } catch (Exception $e) {
            return response($e->getMessage(), 403);
        }

        // Redirect to the originally intended URL
        $returnUrl = $this->identityManager->getSsoReturnUrl($request);

        return redirect()->to($returnUrl);
    }

    public function metadata(Request $request)
    {
        $metadata = $this->identityManager->getMetadata();

        if (empty($metadata)) {
            return response('Metadata not available.', 404);
        }

        return response($metadata)->withHeaders([
            'Content-Type' => 'text/xml',
        ]);
    }
}
