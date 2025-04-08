<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects;

readonly class RemoteIdentity
{
    public function __construct(
        public string $idp,
        public string $uid,
        public string $principalName = '',
        public string $displayName = '',
        public string $email = '',
        public array $data = [],
    ) {}

    public static function fromData(
        string $idp,
        string $uid,
        array $data = [],
        ?string $cn = null,
        ?string $givenName = null,
        ?string $sn = null,
        ?string $displayName = null,
        ?string $eduPersonPrincipalName = null,
        ?string $mail = null,
    ): RemoteIdentity {
        return new RemoteIdentity(
            idp: $idp,
            uid: $uid,
            principalName: $eduPersonPrincipalName
                ?? $mail
                ?? $uid,
            displayName: $displayName
                ?? $cn
                ?? trim(($givenName ?? '').' '.($sn ?? '')),
            email: $mail ?: null,
            data: $data,
        );
    }

    /**
     * Provides an id that is unique within the IdP, i.e., NetID or CWID
     */
    public function id(): string
    {
        return $this->uid;
    }

    /**
     * Provides an id that is unique across Cornell IdPs.
     */
    public function uniqueUid(): string
    {
        return match (true) {
            $this->isWeillIdP() => $this->uid.'_w',
            default => $this->uid,
        };
    }

    /*
     * Returns the eduPersonPrincipalName
     */
    public function principalName(): string
    {
        return $this->principalName;
    }

    /**
     * Returns the primary email (netid@cornell.edu|cwid@med.cornell.edu) if available, otherwise the alias email.
     */
    public function email(): string
    {
        return $this->principalName ?: $this->email;
    }

    /**
     * Returns the display name if available, otherwise the common name, fallback is "givenName sn".
     */
    public function name(): string
    {
        return $this->displayName;
    }

    public function isCornellIdP(): bool
    {
        return str_contains($this->idp, 'cit.cornell.edu');
    }

    public function isWeillIdP(): bool
    {
        return str_contains($this->idp, 'weill.cornell.edu');
    }
}
