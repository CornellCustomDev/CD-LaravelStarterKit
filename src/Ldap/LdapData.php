<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

use Exception;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * An immutable data object representing the LDAP data returned for a user.
 */
class LdapData
{
    public function __construct(
        public string $netid,
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
        // User the defined primary affiliation or the first affiliation in the collection.
        $primaryAffiliation = ($data['cornelleduprimaryaffiliation'] ?? null) ?: $affiliationCollection->shift() ?? '';
        // Secondary affiliation is the first affiliation that is not the primary affiliation.
        $secondaryAffiliation = $affiliationCollection->reject($primaryAffiliation)->first() ?? '';

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
            netid: $data['uid'],
            emplid: $data['cornelleduemplid'] ?? '',
            firstName: $firstName,
            lastName: $lastName,
            // Use preferred display name if it is not null, otherwise fall back on first_name + last_name.
            displayName: $data['displayname'] ?? trim($firstName.' '.$lastName),
            // Only set 'email' if it is not empty.
            email: ($data['mail'] ?? null) ?: null,
            campusPhone: $data['cornelleducampusphone'] ?? null,
            deptName: $data['cornelledudeptname1'] ?? null,
            workingTitle: $data['cornelleduwrkngtitle1'] ?? null,
            primaryAffiliation: $primaryAffiliation ?: null,
            affiliations: $affiliations,
            previousNetids: $data['cornelledupreviousnetids'] ?? null,
            previousEmplids: $data['cornelledupreviousemplids'] ?? null,
            ldapData: $ldapData,
            returnedData: $data,
        );
    }

    /**
     * @throws LdapDataException
     */
    public static function get(string $netid, bool $bustCache = false): ?self
    {
        if (empty($netid)) {
            throw new InvalidArgumentException('LdapData::get requires netid');
        }

        // Return a cached result if we have on and we are not busting the cache.
        $cacheKey = self::class.'::get_'.$netid;
        if ($bustCache) {
            Cache::forget($cacheKey);
        }
        if ($cachedResult = Cache::get($cacheKey)) {
            return $cachedResult;
        }

        if ($ldapData = self::find($netid)) {
            Cache::put($cacheKey, $ldapData, now()->addSeconds(config('ldap.cache_seconds')));
        }

        return $ldapData;
    }

    /**
     * @throws LdapDataException
     */
    public static function find(string $netid, ?bool $debug = false): ?self
    {
        $server = config('ldap.server');
        $connection = ldap_connect($server);
        if (! $connection) {
            throw new LdapDataException('Could not connect to LDAP server.');
        }

        $result = ldap_bind_ext($connection, "uid=$netid", config('ldap.pass'));
        if (! $result) {
            throw new LdapDataException('Could not bind to LDAP server.');
        }

        $parsed_result = ldap_parse_result($connection, $result, $error_code, $matched_dn,
            $error_message) ?: $error_message;
        if ($parsed_result !== true) {
            throw new LdapDataException("Error response from ldap_bind: $parsed_result");
        }

        try {
            $result = ldap_search($connection, config('ldap.base_dn'), "uid=$netid");
            if (! $result) {
                return null;
            }
            $result_entry = ldap_first_entry($connection, $result);
            if (! $result_entry) {
                return null;
            }
            $response = ldap_get_attributes($connection, $result_entry);

            if ($debug) {
                dump(json_encode($response));
            }
            if (! $response) {
                return null;
            }
            $data = self::parseResponse($response);
        } catch (Exception $e) {
            // $this->logError("Error in ldap_search for $netid: " . $e->getMessage());
            throw new LdapDataException($e->getMessage());
        }

        // Load the data into an immutable object.
        return self::make($data);
    }

    /**
     * Parse a response from ldap_search into a simple array.
     */
    public static function parseResponse(?array $response = []): array
    {
        unset($response['dn']);
        $data = [];
        foreach ($response as $key => $value) {
            if (is_numeric($key) || $key == 'count') {
                continue;
            }
            if ($value['count'] == 1) {
                $parsedValue = $value[0];
            } else {
                unset($value['count']);
                $parsedValue = $value;
            }
            // Only populate the field if we have data.
            if (! empty($parsedValue)) {
                $data[$key] = $parsedValue;
            }
        }

        return $data;
    }
}
