<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\DataObjects;

class ShibIdentity
{
    public const SHIB_FIELDS = [
        'Shib_Application_ID', // <vhost>
        'Shib_Authentication_Instant', // YYYY-MM-DDT00:00:00.000Z
        'Shib_Handler', // <SITE_URL>/Shibboleth.sso
        'Shib_Identity_Provider', // https://shibidp.cit.cornell.edu/idp/shibboleth
        'Shib_Session_Expires', // timestamp
        'Shib_Session_Inactivity', // timestamp
        'cn', // John Doe
        'displayName', // John Doe
        'eduPersonAffiliations', // employee;member;staff
        'eduPersonEntitlement',
        'eduPersonPrimaryAffiliation', // staff
        'eduPersonPrincipalName', // netid@cornell.edu
        'eduPersonScopedAffiliation', // employee@cornell.edu;member@cornell.edu;staff@cornell.edu
        'givenName',
        'groups', // rg.cuniv.employee.staff;cu.employee;cit.roc;cit.iws.cs.dfa
        'mail', // alias email
        'sn',
        'uid', // netid
    ];

    public function __construct(
        public readonly string $idp,
        public readonly string $uid,
        public readonly string $displayName = '',
        public readonly string $mail = '',
        public readonly array $serverVars = [],
    ) {}

    public static function fromServerVars(?array $serverVars = null): self
    {
        if (empty($serverVars)) {
            $serverVars = app('request')->server();
        }

        return new ShibIdentity(
            idp: $serverVars['Shib_Identity_Provider'] ?? '',
            uid: $serverVars['uid'] ?? '',
            displayName: $serverVars['displayName'] ?? '',
            mail: $serverVars['mail'] ?? '',
            serverVars: $serverVars,
        );
    }

    public function isCornellIdP(): bool
    {
        return str_contains($this->idp, 'cit.cornell.edu');
    }

    public function isWeillIdP(): bool
    {
        // TODO: Verify this is the correct domain
        return str_contains($this->idp, 'med.cornell.edu');
    }
}
