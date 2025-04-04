# LdapData

A helper for retrieving Cornell University LDAP data.

This helper wraps standard LDAP PHP extension functions, configured for Cornell University. It encapsulates
standard Cornell LDAP attributes in a well-defined data structure.

Environment variables that define the LDAP user and password should be set in the environment. See `.env.example`.

Example usage:

```php
use CornellCustomDev\LaravelStarterKit\Ldap\LdapData;
try {
  $ldapData = LdapData::get($netid);
  $displayName = $ldapData->displayName;
} catch (LdapDataServiceException $e) {
  ...
}
```

The `LdapService::get()` method caches the query for 300 seconds by default, so multiple calls to the service for the
same `$netid` value are not expensive.

Documentation of all currently parsed fields can be found in [LdapData.php](./LdapData.php).

## Legacy Compatibility

[LdapData](./LdapData.php) has a property to provide compatibility with the `LDAP::data()` method from the
legacy `App\Helpers\LDAP` class that exists on many Cornell Laravel sites.

```php
$ldapData = LdapData::get($netid)?->ldapData;
```

The value of this property matches the output of `LDAP::data($netid)`, but with the 'count' and 'count_values' keys
removed.

## Additional LDAP Attributes

`LdapData::get($netid)->returnedData` is an array of all LDAP attributes returned, keyed by attribute name. The set
of attributes is a subset of the attributes documented
at https://confluence.cornell.edu/pages/viewpage.action?spaceKey=IDM&title=Attributes.
