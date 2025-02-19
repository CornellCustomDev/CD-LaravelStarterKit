<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects;

class RemoteIdentity
{
    public function __construct(
        public readonly string $idp,
        public readonly string $uid,
        public readonly string $displayName = '',
        public readonly string $email = '',
        public readonly array $data = [],
    ) {}

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

    public function isCornellIdP(): bool
    {
        return str_contains($this->idp, 'cit.cornell.edu');
    }

    public function isWeillIdP(): bool
    {
        return str_contains($this->idp, 'weill.cornell.edu');
    }
}
