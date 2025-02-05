# Shibboleth Configuration on Media 3

[CUAuth](README.md) is built with consideration of these authentication and authorization components:

- Shibboleth configuration of identity providers
- Apache configuration of locations protected by Shibboleth
- Laravel app configuration of authorization


## Shibboleth ApplicationOverride setup

In `/etc/shibboleth/shibboleth2.xml`, specify identity providers in the `ApplicationDefaults` element. Use `ApplicationOverride` for alternate IdPs. Make sure to include the `MetadataProvider` for each IdP.

Key elements (can be set up for any Media 3 server):
```xml
...
    <ApplicationDefaults ...>
        <Sessions ...>
            <!-- Default IdP-->
            <SSO entityID="https://shibidp.cit.cornell.edu/idp/shibboleth" ...>
            ...
        </Sessions>
        <MetadataProvider url="https://shibidp.cit.cornell.edu/idp/shibboleth" backingFilePath="cornellidp.xml" ... />
        <MetadataProvider url="https://shibidp-test.cit.cornell.edu/idp/shibboleth" backingFilePath="cornell-idp.xml" ... />
        <MetadataProvider url="https://login.weill.cornell.edu/idp/saml2/idp/metadata.php" backingFilePath="weill-idp.xml" ... />
        <MetadataProvider url="https://login-test.weill.cornell.edu/idp/saml2/idp/metadata.php" backingFilePath="weill-test-idp.xml" ... />
        ...
        <ApplicationOverride id="cit-test">
            <Sessions ...>
                <SSO discoveryURL="https://shibidp-test.cit.cornell.edu/idp/shibboleth" ...>
                ...
            </Sessions>
        </ApplicationOverride>
        <ApplicationOverride id="idselect">
            <Sessions ...>
                <SSO discoveryURL="https://idselect.idm.cit.cornell.edu/idselect/select.html" ...>
                ...
            </Sessions>
        </ApplicationOverride>
        <ApplicationOverride id="idselect-test">
            <Sessions ...>
                <SSO discoveryURL="https://idselect-test.idm.cit.cornell.edu/idselect/select.html" ...>
                ...
            </Sessions>
        </ApplicationOverride>
```

Note: If all sites on a server should use the same IdP, modification of `shibboleth2.xml` is not necessary.


## Apache configuration

To utilize a Shibboleth `ApplicationOverride` configuration, the Apache vhost configuration for the site must specify the 
`applicationId`. The vhost configuration is also where it is determined which pages are protected by Shibboleth.

```apache
<Directory /home/sitedev/public_html/mysite/public>
    ...
    AuthType shibboleth
    ShibRequestSetting redirectToSSL 443
    # Laravel will determine when to authenticate
    ShibRequestSetting requireSession 0
    # Set the applicationId to use determine the IdP
    ShibRequestSetting applicationId idselect-test
    require shibboleth
</Directory>
```

Note: If the site uses the default IdP, the `applicationId` is not needed. If all routes for the site should be protected by Shibboleth, `requireSession` can be set to `1`. 

## Laravel configuration

Use ApacheShib middleware to protect routes that need authorization.

```php
// File: routes/web.php
use CornellCustomDev\LaravelStarterKit\CUAuth\Middleware\ApacheShib;

Route::get('/', fn () => view('welcome'))->name('welcome');

Route::get('login', fn () => Redirect::route('cu-auth.shibboleth-login'))->name('login');
Route::get('logout', fn () => Redirect::route('cu-auth.shibboleth-logout'))->name('logout');

Route::group(['middleware' => [ApacheShib::class, AppTesters::class]], function() {
    // Routes that require Shibboleth authentication + any authorization policies
}
```

Note: If all pages on the site are protected by Shibboleth, the login and logout routes are not relevant.

## PHP configuration

On Media 3 servers, Shibboleth attributes are available only on sites served with suphp. Sites using php-cgi
only pass the validated user identifier. If you need to determine which IdP authenticated the user, you must use suphp.

> **Important**: Selection of suphp / php-cgi is set server-wide for each PHP version. Additionally, file and directory permissions must be properly set if using suphp. Media 3 support can help with this.
