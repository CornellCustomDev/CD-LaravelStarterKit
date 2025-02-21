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

    public static function fromAcsPost(): RemoteIdentity
    {
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

        $remoteIdentity = new RemoteIdentity(
            idp: 'cit.cornell.edu',
            uid: $attributes['uid'][0] ?? '',
            displayName: $attributes['displayName'][0]
            ?? $attributes['cn'][0]
            ?? trim(($attributes['givenName'][0] ?? '').' '.($attributes['sn'][0] ?? '')),
            email: $attributes['eduPersonPrincipalName'][0]
            ?? $attributes['mail'][0] ?? '',
            data: $attributes,
        );

        return $remoteIdentity;
    }

    /**
     * @throws Exception
     */
    public function storeIdentity(?RemoteIdentity $remoteIdentity = null): ?RemoteIdentity
    {
        $remoteIdentity ??= self::fromAcsPost();

        session()->put('remoteIdentity', $remoteIdentity);

        return $remoteIdentity;
    }

    public function hasIdentity(?Request $request = null): bool
    {
        return ! empty(self::getIdentity());
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

    public function getSloUrl(string $returnUrl): string
    {
        return $returnUrl;
    }

    /**
     * @throws Exception
     */
    public static function getMetadata(): string
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
}
