<?php

namespace CornellCustomDev\LaravelStarterKit\Ldap;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use LDAP\Connection;
use LDAP\Result;
use LDAP\ResultEntry;

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
     * Search LDAP for users with netids starting with the given word, cached by default.
     *
     * @throws LdapDataException
     */
    public static function getByNetid(string $netid, bool $bustCache = false): ?LdapData
    {
        if (empty(trim($netid))) {
            throw new InvalidArgumentException('LdapSearch::getByNetid requires a search term');
        }

        /** @var Collection<string,LdapData> $results */
        $results = self::search("(uid=$netid*)", $bustCache);

        return $results->first();
    }

    /**
     * @throws LdapDataException
     */
    public static function getByEmail(string $email, bool $bustCache = false): ?LdapData
    {
        if (empty(trim($email))) {
            throw new InvalidArgumentException('LdapSearch::getByEmail requires a search term');
        }

        /** @var Collection<string,LdapData> $results */
        $results = self::search("(|(mail=$email)(edupersonprincipalname=$email))", $bustCache);

        return $results->first();
    }

    /**
     * Search LDAP, cached by default, returning a collection of LdapData objects.
     *
     * @throws InvalidArgumentException
     * @throws LdapDataException
     */
    public static function search(
        string $filter,
        bool $bustCache = false,
        ?array $attributes = null,
        ?bool $withLdapData = true,
    ): Collection {
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
            callback: fn () => self::performSearch($filter, $attributes, $withLdapData),
        );
    }

    /**
     * Search LDAP without caching, returning a collection of LdapData objects.
     *
     * @throws LdapDataException
     */
    public static function bulkSearch(
        string $filter,
        ?array $attributes = null,
        ?bool $withLdapData = false,
    ): Collection {
        // Trap for empty strings
        if (empty(trim($filter))) {
            throw new InvalidArgumentException('LdapSearch::bulkSearch requires a search term');
        }
        $attributes ??= self::DEFAULT_ATTRIBUTES;

        return self::performSearch($filter, $attributes, $withLdapData);
    }

    /**
     * Perform an LDAP search with the given filter, returning a collection of LdapData objects.
     *
     * @throws LdapDataException
     */
    private static function performSearch(
        string $filter,
        ?array $attributes = null,
        bool $withLdapData = true,
    ): Collection {
        $attributes ??= self::DEFAULT_ATTRIBUTES;

        try {
            $server = config('ldap.server');
            $connection = ldap_connect($server);
            if (! $connection) {
                throw new LdapDataException('Could not connect to LDAP server.');
            }

            // Set options for performance
            ldap_set_option($connection, LDAP_OPT_SIZELIMIT, 1100);
            ldap_set_option($connection, LDAP_OPT_TIMELIMIT, 3);
            // Assure LDAPv3 and for security disable referrals
            ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

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
            if ($result === false) {
                // If there is an error, the result will be false
                throw new LdapDataException('Error performing LDAP search: '.ldap_error($connection));
            }

            $entry_count = ldap_count_entries($connection, $result);
            if (empty($entry_count)) {
                return collect();
            }

            return self::parseSearchResults($connection, $result, $withLdapData);
        } catch (Exception $e) {
            throw new LdapDataException($e->getMessage());
        } finally {
            // Close the LDAP connection
            if (isset($connection) && is_resource($connection)) {
                ldap_close($connection);
            }
        }
    }

    /**
     * Parse LDAP search results into a collection of LdapData objects.
     */
    protected static function parseSearchResults(
        Connection $connection,
        Result $result,
        bool $withLdapData,
    ): Collection {
        $results = collect();

        $entry = ldap_first_entry($connection, $result);
        if ($entry === false) {
            // If there is an error, the ldap_first_entry result will be false
            throw new LdapDataException('Error getting LDAP entry: '.ldap_error($connection));
        }

        while ($entry) {
            $parsedEntry = self::parseEntry($connection, $entry);

            if (isset($parsedEntry['uid'])) {
                $ldapData = LdapData::make($parsedEntry, $withLdapData);
                if ($ldapData) {
                    $results->put($parsedEntry['uid'], $ldapData);
                }
            }

            // Move to next entry
            $entry = ldap_next_entry($connection, $entry);

            // Periodically collect garbage
            if ($results->count() % 100 === 0) {
                gc_collect_cycles();
            }
        }

        return $results;
    }

    /**
     * Parse an entry from ldap_search into a simple array.
     */
    public static function parseEntry(Connection $connection, ResultEntry $entry): array
    {
        $attributes = ldap_get_attributes($connection, $entry);

        return self::normalizeAttributes($attributes);
    }

    /**
     * Normalize LDAP attributes array into a simple key-value array.
     */
    public static function normalizeAttributes(array $attributes): array
    {
        unset($attributes['dn']);
        $data = [];
        foreach ($attributes as $key => $value) {
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
