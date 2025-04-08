<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

/**
 * An immutable data object representing the LDAP data returned for a user.
 */
class LdapData
{
    public function __construct(
        public string $uid,
        public string $principalName,
        public string $emplid,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $displayName,
        public ?string $email,
        public ?string $campusPhone,
        public ?string $deptName,
        public ?string $workingTitle,
        public ?string $primaryAffiliation,
        public ?array $affiliations,
        public ?array $previousNetids,
        public ?array $previousEmplids,
        public array $ldapData = [],
        public array $returnedData = []
    ) {}

    /**
     * Create a new LdapData object from an array of LDAP data.
     *
     * See https://confluence.cornell.edu/display/IDM/Attributes
     */
    public static function make(array $data): ?LdapData
    {
        // Use preferred first name if it is not null, otherwise use givenName.
        $firstName = ($data['cornelleduprefgivenname'] ?? null) ?: ($data['givenName'] ?? null);

        // Use preferred last name if it is not null, otherwise use sn.
        $lastName = ($data['cornelleduprefsn'] ?? null) ?: ($data['sn'] ?? null);

        // Affiliations can be a string, an array, or not set
        $affiliationCollection = collect(($data['cornelleduaffiliation'] ?? null) ?: null);
        $affiliations = $affiliationCollection->toArray();

        // Use the defined primary affiliation or the first affiliation in the collection.
        $primaryAffiliation = ($data['cornelleduprimaryaffiliation'] ?? null) ?: $affiliationCollection->shift() ?? '';

        // Secondary affiliation is the first affiliation that is not the primary affiliation.
        $secondaryAffiliation = $affiliationCollection->reject($primaryAffiliation)->first() ?? '';

        // Process previousNetids: always convert the comma-separated string to an array.
        $previousNetIds = collect(explode(',', $data['cornelledupreviousnetids'] ?? ''))
            ->map(fn ($id) => trim($id))->filter()->all();

        // Same for previousEmplids
        $previousEmplids = collect(explode(',', $data['cornelledupreviousemplids'] ?? ''))
            ->map(fn ($id) => trim($id))->filter()->all();

        // User may have exercised FERPA right to suppress name.
        if (empty($firstName) && empty($lastName)) {
            $firstName = 'Cornell';
            $lastName = $primaryAffiliation === 'student' ? 'Student' : 'User';
        }

        // Format an array to match the old LDAP::data function
        $ldapData = [
            'emplid' => $data['cornelleduemplid'] ?? '',
            'firstname' => $firstName ?? '',
            'lastname' => $lastName ?? '',
            'name' => trim("$firstName $lastName"),
            'email' => $data['mail'] ?? '',
            'campusphone' => $data['cornelleducampusphone'] ?? '',
            'netid' => $data['uid'],
            'deptname' => $data['cornelledudeptname1'] ?? '',
            'primaryaffiliation' => $primaryAffiliation,
            'secondaryaffiliation' => $secondaryAffiliation,
            'wrkngtitle' => $data['cornelleduwrkngtitle1'] ?? '',
            'affiliations' => $affiliations,
            'previousnetids' => $data['cornelledupreviousnetids'] ?? null,
            'previousemplids' => $data['cornelledupreviousemplids'] ?? null,
        ];

        return new LdapData(
            uid: $data['uid'],
            principalName: $data['edupersonprincipalname']
                ?? $data['uid'].'@cornell.edu',
            emplid: $data['cornelleduemplid'] ?? '',

            firstName: $firstName,
            lastName: $lastName,

            // Use preferred display name if it is not null, otherwise fall back on first_name + last_name.
            displayName: $data['displayname']
                ?? trim($firstName.' '.$lastName),

            // Only set 'email' if it is not empty.
            email: ($data['mail'] ?? null) ?: null,

            campusPhone: $data['cornelleducampusphone'] ?? null,
            deptName: $data['cornelledudeptname1'] ?? null,
            workingTitle: $data['cornelleduwrkngtitle1'] ?? null,
            primaryAffiliation: $primaryAffiliation ?: null,
            affiliations: $affiliations,
            previousNetids: $previousNetIds ?: null,
            previousEmplids: $previousEmplids ?: null,
            ldapData: $ldapData,
            returnedData: $data,
        );
    }

    /**
     * Provides an id that is unique within the IdP, i.e., NetID or CWID
     */
    public function id(): string
    {
        return $this->uid;
    }

    /*
     * Returns the eduPersonPrincipalName
     */
    public function principalName(): string
    {
        return $this->principalName;
    }

    /**
     * Returns the primary email (netid@cornell.edu) if available, otherwise the alias email.
     */
    public function email(): string
    {
        return $this->email ?: $this->principalName;
    }

    /**
     * Returns the display name if available, otherwise the common name, fallback is "givenName sn".
     */
    public function name(): string
    {
        return $this->displayName;
    }
}
