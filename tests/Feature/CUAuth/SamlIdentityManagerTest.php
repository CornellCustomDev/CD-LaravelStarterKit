<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use CornellCustomDev\LaravelStarterKit\Tests\Feature\FeatureTestCase;
use Illuminate\Support\Facades\File;
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
            ->expectsQuestion('What would you like to install or update?', ['certs'])
            ->expectsConfirmation('Proceed with installation?', 'yes')
            ->expectsOutputToContain('Installation complete.')
            ->assertSuccessful();

        // Set the idp_cert to the test cert location
        $basePath = $this->getApplicationBasePath();
        config(['php-saml-toolkit.idp.x509cert' => File::get("$basePath/storage/app/keys/idp_cert.pem")]);
        $this->assertStringContainsString('test-idp-cert-contents', config('php-saml-toolkit.idp.x509cert'));

        $url = (new SamlIdentityManager)->getSsoUrl('/');

        $this->assertStringContainsString('https://shibidp-test.cit.cornell.edu/idp/profile/SAML2/Redirect/SSO?SAMLRequest=', $url);
    }

    public function testCanGetMetadata()
    {
        $metadata = (new SamlIdentityManager)->getMetadata();

        $this->assertStringContainsString('entityID="https://localhost/sso"', $metadata);
    }
}
