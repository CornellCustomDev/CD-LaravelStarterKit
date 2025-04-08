<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

/**
 * Class for searching Cornell LDAP directory.
 */
class LdapSearch
{
    /**
     * Default attributes to retrieve from LDAP.
     *
     *  See https://confluence.cornell.edu/display/IDM/Attributes
     */
    public const DEFAULT_ATTRIBUTES = [
        'uid',
        'edupersonprincipalname',
        'displayName',
        'givenName',
        'sn',
        'mail',
        'cornelleduprimaryaffiliation',
        'cornelleduaffiliation',
        'cornelleduemplid',
        'cornelledudeptname1',
        'cornelleduwrkngtitle1',
        'cornelleducampusphone',
        'cornelleduprefgivenname',
        'cornelleduprefsn',
        'cornelledupreviousnetids',
        'cornelledupreviousemplids',
    ];

    /**
     * @throws LdapDataException
     */
    public static function getByNetid(string $netid, bool $bustCache = false): ?LdapData
    {
        if (empty(trim($netid))) {
            throw new InvalidArgumentException('LdapSearch::getByNetid requires a search term');
        }

        return self::search("(uid=$netid*)", $bustCache)?->first();
    }

    /**
     * @throws LdapDataException
     */
    public static function getByEmail(string $email, bool $bustCache = false): ?LdapData
    {
        if (empty(trim($email))) {
            throw new InvalidArgumentException('LdapSearch::getByEmail requires a search term');
        }

        return self::search("(|(mail=$email)(edupersonprincipalname=$email))", $bustCache)?->first();
    }

    /**
     * Search LDAP for users with netids starting with the given word, cached by default.
     *
     * @throws InvalidArgumentException
     * @throws LdapDataException
     */
    public static function search(string $filter, bool $bustCache = false, ?array $attributes = null): ?Collection
    {
        // Trap for empty strings
        if (empty(trim($filter))) {
            throw new InvalidArgumentException('LdapSearch::search requires a search term');
        }
        $attributes ??= self::DEFAULT_ATTRIBUTES;

        $cacheKey = 'LdapSearch::search_'.md5($filter);
        if ($bustCache) {
            Cache::forget($cacheKey);
        }

        return Cache::remember(
            key: $cacheKey,
            ttl: now()->addSeconds(config('ldap.cache_seconds')),
            callback: fn () => self::performSearch($filter, $attributes),
        );
    }

    /**
     * Perform an LDAP search with the given filter, returning a collection of LdapData objects.
     *
     * @throws LdapDataException
     */
    private static function performSearch(string $filter, ?array $attributes = null): ?Collection
    {
        $attributes ??= self::DEFAULT_ATTRIBUTES;

        try {
            $server = config('ldap.server');
            $connection = ldap_connect($server);
            if (! $connection) {
                throw new LdapDataException('Could not connect to LDAP server.');
            }

            // Set options for performance
            ldap_set_option($connection, LDAP_OPT_SIZELIMIT, 1000);
            ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 3);

            // Bind to the LDAP server
            $result = ldap_bind_ext($connection, 'uid='.config('ldap.user'), config('ldap.pass'));
            if (! $result) {
                throw new LdapDataException('Could not bind to LDAP server.');
            }

            // Confirm that the bind was successful
            $parsed_result = ldap_parse_result($connection, $result, $error_code, $matched_dn,
                $error_message) ?: $error_message;
            if ($parsed_result !== true) {
                throw new LdapDataException("Error response from ldap_bind: $parsed_result");
            }

            // Perform the LDAP search
            $result = ldap_search($connection, config('ldap.base_dn'), $filter, $attributes);
            if (! $result) {
                return null;
            }

            $entries = ldap_get_entries($connection, $result);
            if (! $entries || $entries['count'] === 0) {
                return null;
            }

            return self::parseSearchResults($entries);

        } catch (Exception $e) {
            throw new LdapDataException($e->getMessage());
        } finally {
            if (isset($connection) && $connection) {
                ldap_close($connection);
            }
        }
    }

    /**
     * Parse LDAP search results into a collection of LdapData objects.
     *
     * @param  array  $entries  Raw LDAP search results
     * @return Collection Collection of LdapData objects indexed by uid
     */
    protected static function parseSearchResults(array $entries): Collection
    {
        $count = $entries['count'];
        $results = collect();

        for ($i = 0; $i < $count; $i++) {
            $entry = $entries[$i];
            $parsedEntry = self::parseEntry($entry);

            if (isset($parsedEntry['uid'])) {
                $ldapData = LdapData::make($parsedEntry);
                if ($ldapData) {
                    $results->put($parsedEntry['uid'], $ldapData);
                }
            }
        }

        return $results;
    }

    /**
     * Parse an entry from ldap_search into a simple array.
     */
    public static function parseEntry(?array $entry = []): array
    {
        unset($entry['dn']);
        $data = [];
        foreach ($entry as $key => $value) {
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
