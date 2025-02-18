<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects;

use OneLogin\Saml2\Auth;

class SamlIdentity
{
    public const SAML_FIELDS = [
        'uid',
    ];

    public function __construct(
        public readonly string $idp,
        public readonly string $uid,
        public readonly string $displayName = '',
        public readonly string $email = '',
        public readonly array $attributes = [],
    ) {}

    public static function fromAuth(Auth $auth): ?self
    {
        if (! $auth->isAuthenticated()) {
            return null;
        }

        $attributes = $auth->getAttributes();

        return new SamlIdentity(
            idp: '',
            uid: $attributes['uid'][0] ?? '',
            displayName: $attributes['displayName'][0] ?? '',
            email: $attributes['email'][0] ?? '',
            attributes: $attributes,
        );
    }

    public static function getRemoteUser(): ?string
    {
        // TODO: Implement getRemoteUser() method
        return null;
    }
}
