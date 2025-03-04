<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Managers;

use CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects\RemoteIdentity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\AuthnRequest;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\ValidationError;

class SamlIdentityManager implements IdentityManager
{
    // Shibboleth fields generally available from either cit or weill IdPs.
    public const SAML_FIELDS = [
        'eduPersonPrimaryAffiliation', // staff|student|...
        'cn', // John R. Doe
        'eduPersonPrincipalName', // netid@cornell.edu
        'givenName', // John
        'sn', // Doe
        'displayName', // John Doe
        'uid', // netid
        'eduPersonOrgDN', // o=Cornell University,c=US
        'mail', // alias? email
        'eduPersonAffiliation', // ['employee', 'staff', ...]
        'eduPersonScopedAffiliation', // [employee@cornell.edu, staff@cornell.edu, ...]
        'eduPersonEntitlement',
    ];

    public function hasIdentity(): bool
    {
        return ! empty($this->getIdentity());
    }

    public function getIdentity(): ?RemoteIdentity
    {
        /** @var RemoteIdentity|null $remoteIdentity */
        $remoteIdentity = session()->get('remoteIdentity');

        return $remoteIdentity;
    }

    /**
     * @throws Exception
     */
    public function storeIdentity(): ?RemoteIdentity
    {
        $remoteIdentity = $this->retrieveIdentity();
        session()->put('remoteIdentity', $remoteIdentity);

        return $remoteIdentity;
    }

    /**
     * @throws Exception
     */
    public function getSsoUrl(string $redirectUrl): string
    {
        try {
            $settings = new Settings(config('php-saml-toolkit'));
        } catch (Exception $e) {
            throw new Exception('Invalid SAML settings: '.$e->getMessage());
        }
        $authRequest = new AuthnRequest($settings);

        $url = $settings->getIdPData()['singleSignOnService']['url'];
        $query = Arr::query([
            'SAMLRequest' => $authRequest->getRequest(),
            'RelayState' => $redirectUrl,
        ]);

        return $url.'?'.$query;
    }

    public function getSsoReturnUrl(Request $request): string
    {
        return $request->input('RelayState', '/');
    }

    public function getSloUrl(string $returnUrl): string
    {
        return $returnUrl;
    }

    /**
     * @throws Exception
     */
    public function getMetadata(): ?string
    {
        try {
            $settings = new Settings(config('php-saml-toolkit'), true);
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);
        } catch (Exception $e) {
            throw new Exception('Invalid SP metadata: '.$e->getMessage());
        }

        if (! empty($errors)) {
            throw new Exception('Invalid SP metadata: '.implode(', ', $errors));
        }

        return $metadata;
    }

    private function retrieveIdentity(?array $attributes = null): RemoteIdentity
    {
        if (empty($attributes)) {
            try {
                $auth = new Auth(settings: config('php-saml-toolkit'));
                $auth->processResponse();
            } catch (Error|ValidationError $e) {
                throw new Exception('SAML Response Error: '.$e->getMessage());
            }

            $errors = $auth->getErrors();
            if (! empty($errors)) {
                throw new Exception('SAML Response Errors: '.implode(', ', $errors));
            }
            if (! $auth->isAuthenticated()) {
                throw new Exception('SAML Response not authenticated');
            }

            $attributes = $auth->getAttributesWithFriendlyName();
        }

        return RemoteIdentity::fromData(
            idp: 'cit.cornell.edu',
            uid: $attributes['uid'][0] ?? '',
            data: $attributes,
            cn: $attributes['cn'][0] ?? null,
            givenName: $attributes['givenName'][0] ?? null,
            sn: $attributes['sn'][0] ?? null,
            displayName: $attributes['displayName'][0] ?? null,
            eduPersonPrincipalName: $attributes['eduPersonPrincipalName'][0] ?? null,
            mail: $attributes['mail'][0] ?? null,
        );
    }
}
