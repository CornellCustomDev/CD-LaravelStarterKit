# LDAP Components

A set of helpers for retrieving Cornell University LDAP data.

This package provides two main classes:
- `LdapSearch`: Handles LDAP queries and caching
- `LdapData`: An immutable data object that encapsulates Cornell LDAP attributes in a well-defined structure

Environment variables that define the LDAP user and password should be set in the environment. See `.env.example`.

Example usage:

```php
use CornellCustomDev\LaravelStarterKit\Ldap\LdapSearch;

try {
  $ldapData = LdapSearch::getByNetid($netid);
  $displayName = $ldapData->displayName;
} catch (LdapDataException $e) {
  // Handle exceptions
}
```

The `LdapSearch::getByNetid()` method caches the query for a configurable duration (default 300 seconds), so multiple calls for the same `$netid` value are not expensive.

You can also search for users by netid prefix:

```php
$users = LdapSearch::searchByNetid('abc');  // returns Collection of LdapData for netids starting with 'abc'
```

Documentation of all currently parsed fields can be found in [LdapData.php](./LdapData.php).

## Legacy Compatibility

[LdapData](./LdapData.php) has a property to provide compatibility with the `LDAP::data()` method from the
legacy `App\Helpers\LDAP` class that exists on many Cornell Laravel sites.

```php
$ldapData = LdapSearch::getByNetid($netid)?->ldapData;
```

The value of this property matches the output of `LDAP::data($netid)`, but with the 'count' and 'count_values' keys
removed.

## Additional LDAP Attributes

`LdapSearch::getByNetid($netid)->returnedData` is an array of all LDAP attributes returned, keyed by attribute name. The set
of attributes is a subset of the attributes documented
at https://confluence.cornell.edu/pages/viewpage.action?spaceKey=IDM&title=Attributes.
