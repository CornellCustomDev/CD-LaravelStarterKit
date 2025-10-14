<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Events\CUAuthenticated;
use CornellCustomDev\LaravelStarterKit\CUAuth\Listeners\AuthorizeUser;
use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use CornellCustomDev\LaravelStarterKit\Tests\Feature\FeatureTestCase;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;

class SamlIdentityManagerTest extends FeatureTestCase
{
    public function testCanTestSettingsAreInvalid()
    {
        $settings = [];

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Invalid array settings');
        $settings = new Settings($settings);
    }

    public function testDefaultConfigSettingsAreValid()
    {
        $settings = config('php-saml-toolkit');
        $settings['idp']['x509cert'] = 'TEST';

        // Assert no exceptions or errors
        $this->assertInstanceOf(Settings::class, new Settings($settings));
    }

    public function testCanGetSsoUrl()
    {
        $this->artisan(StarterKitServiceProvider::PACKAGE_NAME.':install')
            ->expectsQuestion('What would you like to install or update?', ['php-saml-toolkit', 'certs'])
            ->expectsConfirmation('Proceed with installation?', 'yes')
            ->expectsOutputToContain('Installation complete.')
            ->assertSuccessful();

        $_ENV['SAML_IDP_BASEURL'] = 'https://shibidp-test.cit.cornell.edu/idp';
        $this->app['config']->set('php-saml-toolkit', require config_path('php-saml-toolkit.php'));

        $url = (new SamlIdentityManager)->getSsoUrl('/');

        $this->assertStringContainsString('https://shibidp-test.cit.cornell.edu/idp/profile/SAML2/Redirect/SSO?SAMLRequest=', $url);
        $this->assertStringContainsString('test-idp-cert-contents', config('php-saml-toolkit.idp.x509cert'));
    }

    public function testCanGetWeillSsoUrl()
    {
        $this->artisan(StarterKitServiceProvider::PACKAGE_NAME.':install')
            ->expectsQuestion('What would you like to install or update?', ['php-saml-toolkit', 'certs-weill'])
            ->expectsConfirmation('Proceed with installation?', 'yes')
            ->expectsOutputToContain('Installation complete.')
            ->assertSuccessful();

        $_ENV['SAML_IDP_BASEURL'] = 'https://login-test.weill.cornell.edu/idp';
        $this->app['config']->set('php-saml-toolkit', require config_path('php-saml-toolkit.php'));

        $url = (new SamlIdentityManager)->getSsoUrl('/');

        $this->assertStringContainsString('https://login-test.weill.cornell.edu/idp/profile/SAML2/Redirect/SSO?SAMLRequest=', $url);
        $this->assertStringContainsString('test-weill-idp-cert-contents', config('php-saml-toolkit.idp.x509cert'));
    }

    public function testCanGetMetadata()
    {
        $metadata = (new SamlIdentityManager)->getMetadata();

        $this->assertStringContainsString('entityID="https://localhost/sso"', $metadata);
    }

    public function testSamlIdentity()
    {
        config(['php-saml-toolkit.idp.entityId' => 'https://shibidp-test.cit.cornell.edu/idp/shibboleth']);
        $saml = (new SamlIdentityManager)->retrieveIdentity([
            'uid' => ['netid'],
            'mail' => ['netid@cornell.edu'],
        ]);

        $this->assertTrue($saml->isCornellIdP());
        $this->assertFalse($saml->isWeillIdP());
        $this->assertEquals('netid', $saml->id());
        $this->assertEquals('netid@cornell.edu', $saml->email());
    }

    public function testSamlWeillIdentity()
    {
        config(['php-saml-toolkit.idp.entityId' => 'https://login-test.weill.cornell.edu/idp/shibboleth']);
        $saml = (new SamlIdentityManager)->retrieveIdentity([
            'uid' => ['cwid'],
            'mail' => ['cwid@med.cornell.edu'],
        ]);

        $this->assertFalse($saml->isCornellIdP());
        $this->assertTrue($saml->isWeillIdP());
        $this->assertEquals('cwid', $saml->id());
        $this->assertEquals('cwid@med.cornell.edu', $saml->email());
    }

    public function testIdentityNames()
    {
        $identityManager = new SamlIdentityManager;
        $remoteIdentity = $identityManager->retrieveIdentity([
            'uid' => ['netid'],
            'displayName' => ['Test User'],
        ]);
        $this->assertEquals('Test User', $remoteIdentity->name());

        $remoteIdentity = $identityManager->retrieveIdentity([
            'uid' => ['netid'],
            'cn' => ['Test User'],
        ]);
        $this->assertEquals('Test User', $remoteIdentity->name());

        $remoteIdentity = $identityManager->retrieveIdentity([
            'uid' => ['netid'],
            'givenName' => ['Test'],
            'sn' => ['User'],
        ]);
        $this->assertEquals('Test User', $remoteIdentity->name());
    }

    public function testIdentityOidValues()
    {
        $identityManager = new SamlIdentityManager;
        $remoteIdentity = $identityManager->retrieveIdentity([
            SamlIdentityManager::SAML_FIELDS['cn'] => ['Test User'],
            SamlIdentityManager::SAML_FIELDS['eduPersonPrincipalName'] => ['netid@cornell.edu'],
            SamlIdentityManager::SAML_FIELDS['uid'] => ['netid'],
        ]);

        // Confirm we sent the correct values to RemoteIdentity
        $this->assertEquals([
            'urn:oid:2.5.4.3' => ['Test User'],
            'urn:oid:1.3.6.1.4.1.5923.1.1.1.6' => ['netid@cornell.edu'],
            'urn:oid:0.9.2342.19200300.100.1.1' => ['netid'],
        ], $remoteIdentity->data);

        $this->assertEquals('Test User', $remoteIdentity->name());
        $this->assertEquals('netid', $remoteIdentity->id());
        $this->assertEquals('netid@cornell.edu', $remoteIdentity->email());
    }

    public function testAuthorizeUser()
    {
        config(['php-saml-toolkit.idp.entityId' => 'https://login-test.weill.cornell.edu/idp/shibboleth']);
        $identityManager = new SamlIdentityManager;
        $remoteIdentity = $identityManager->retrieveIdentity([
            'uid' => ['netid'],
            'displayName' => ['Test User'],
            'mail' => ['cwid@med.cornell.edu'],
        ]);
        $event = new CUAuthenticated('netid@cornell.edu');
        $listener = new AuthorizeUser($identityManager);
        $listener->handle($event, $remoteIdentity);

        $this->assertTrue(auth()->check());
        $this->assertEquals('Test User', auth()->user()->name);
        $this->assertEquals('cwid@med.cornell.edu', auth()->user()->email);
    }
}
