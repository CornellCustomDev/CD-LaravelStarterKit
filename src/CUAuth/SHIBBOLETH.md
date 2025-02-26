# Media 3 Server Configuration for Shibboleth 

[CUAuth](README.md) can be configured to protect specific routes with Shibboleth authentication and manage multiple 
Identity Providers (IdPs).

Below is server configuration for using [CUAuth](README.md) with mod_shib on Media 3.

## Apache configuration

The vhost configuration determines which pages are protected by Shibboleth. The configuration below allows
Laravel to use routing to determine when to authenticate the user.

```apache
# File: /etc/apache2/conf.d/userdata/ssl/2_4/<username>/<sitename>/<sitename>.conf
<Directory /home/<username>/public_html/<site>/public>
    ...
    AuthType shibboleth
    ShibRequestSetting redirectToSSL 443
    # Laravel will determine when to authenticate, set to 1 if all pages should be protected
    ShibRequestSetting requireSession 0
    # Set the applicationId if not using the default IdP
    # ShibRequestSetting applicationId idselect-test
    require shibboleth
</Directory>
```

Use [CUAuth middleware](README.md#single-sign-on) to protect routes that need authorization. If all routes for the site 
should be protected by Shibboleth, `requireSession` can be set to `1`. 

To utilize a Shibboleth `ApplicationOverride` configuration, the Apache vhost configuration for the site must specify 
the `applicationId`, which should be configured in shibboleth2.xml ([see below](#shibboleth-with-multiple-idps)).


## PHP configuration

On Media 3 servers, Shibboleth attributes are available only on sites served with suphp. Sites using php-cgi
only pass the validated user identifier. If you need to determine which IdP authenticated the user, you must use suphp.

> **Important**: Selection of suphp / php-cgi is set server-wide for each PHP version. Additionally, file and directory permissions must be properly set if using suphp. Media 3 support can help with this.


## Shibboleth with Multiple IdPs

In `/etc/shibboleth/shibboleth2.xml`, specify identity providers in the `ApplicationDefaults` element. Use `ApplicationOverride` for alternate IdPs. Make sure to include the `MetadataProvider` for each IdP.

> Note: If all sites on a server should use the same IdP, modification of `shibboleth2.xml` is not necessary.

Key elements (can be set up for any Media 3 server):
```xml
<!-- File: /etc/shibboleth/shibboleth2.xml -->
...
    <ApplicationDefaults ...>
        <Sessions ...>
            <!-- Default IdP-->
            <SSO entityID="https://shibidp.cit.cornell.edu/idp/shibboleth" ...>
            ...
        </Sessions>
        <MetadataProvider url="https://shibidp.cit.cornell.edu/idp/shibboleth" backingFilePath="cornellidp.xml" ... />
        <MetadataProvider url="https://shibidp-test.cit.cornell.edu/idp/shibboleth" backingFilePath="cornell-test-idp.xml" ... />
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
