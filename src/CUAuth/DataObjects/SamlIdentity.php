<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects;

class SamlIdentity
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

    public function __construct(
        public readonly string $idp,
        public readonly string $uid,
        public readonly string $displayName = '',
        public readonly string $email = '',
        public readonly array $attributes = [],
    ) {}

    /**
     * Provides a uid that is unique across Cornell IdPs.
     */
    public function uniqueUid(): string
    {
        return $this->uid;
    }

    /**
     * Returns the primary email (netid@cornell.edu|cwid@med.cornell.edu) if available, otherwise the alias email.
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     * Returns the display name if available, otherwise the common name, fallback is "givenName sn".
     */
    public function name(): string
    {
        return $this->displayName;
    }
}
