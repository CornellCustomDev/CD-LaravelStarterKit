<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects;

use Illuminate\Http\Request;

class ShibIdentity
{
    // Shibboleth fields generally available from either cit or weill IdPs.
    public const SHIB_FIELDS = [
        'Shib_Application_ID', // <vhost|applicationId>
        'Shib_Authentication_Instant', // YYYY-MM-DDT00:00:00.000Z
        'Shib_Identity_Provider', // https://shibidp.cit.cornell.edu/idp/shibboleth|https://login.weill.cornell.edu/idp
        'Shib_Session_Expires', // timestamp
        'Shib_Session_Inactivity', // timestamp
        'displayName', // John Doe
        'eduPersonAffiliations', // employee;member;staff
        'eduPersonPrincipalName', // netid@cornell.edu|cwid@med.cornell.edu
        'eduPersonScopedAffiliation', // employee@[med.]cornell.edu;member@[med.]cornell.edu;staff@cornell.edu
        'givenName', // John
        'mail', // alias email
        'sn', // Doe
        'uid', // netid|cwid
    ];

    public function __construct(
        public readonly string $idp,
        public readonly string $uid,
        public readonly string $displayName = '',
        public readonly string $email = '',
        public readonly array $serverVars = [],
    ) {}

    /**
     * Shibboleth server variables will be retrieved from the request if not provided.
     */
    public static function fromServerVars(?array $serverVars = null): self
    {
        if (empty($serverVars)) {
            $serverVars = app('request')->server();
        }

        return new ShibIdentity(
            idp: $serverVars['Shib_Identity_Provider'] ?? '',
            uid: $serverVars['uid'] ?? '',
            displayName: $serverVars['displayName']
                ?? $serverVars['cn']
                ?? trim(($serverVars['givenName'] ?? '').' '.($serverVars['sn'] ?? '')),
            email: $serverVars['eduPersonPrincipalName']
                ?? $serverVars['mail'] ?? '',
            serverVars: $serverVars,
        );
    }

    public static function getRemoteUser(?Request $request = null): ?string
    {
        if (empty($request)) {
            $request = app('request');
        }

        // If this is a local development environment, allow the local override.
        $remote_user_override = self::getRemoteUserOverride();

        // Apache mod_shib populates the remote user variable if someone is logged in.
        return $request->server(config('cu-auth.apache_shib_user_variable')) ?: $remote_user_override;
    }

    public static function getRemoteUserOverride(): ?string
    {
        // If this is a local development environment, allow the local override.
        return app()->isLocal() ? config('cu-auth.remote_user_override') : null;
    }

    public function isCornellIdP(): bool
    {
        return str_contains($this->idp, 'cit.cornell.edu');
    }

    public function isWeillIdP(): bool
    {
        return str_contains($this->idp, 'weill.cornell.edu');
    }

    /**
     * Provides a uid that is unique across Cornell IdPs.
     */
    public function uniqueUid(): string
    {
        return match (true) {
            $this->isCornellIdP() => $this->uid,
            $this->isWeillIdP() => $this->uid.'_w',
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
}
