<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature\CUAuth;

use CornellCustomDev\LaravelStarterKit\CUAuth\Managers\SamlIdentityManager;
use CornellCustomDev\LaravelStarterKit\Tests\Feature\FeatureTestCase;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Settings;

class PhpSamlTest extends FeatureTestCase
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
        $url = SamlIdentityManager::getSsoUrl('/');

        $this->assertStringContainsString('https://shibidp-test.cit.cornell.edu/idp/profile/SAML2/Redirect/SSO?SAMLRequest=', $url);
    }

    public function testCanGetMetadata()
    {
        $metadata = SamlIdentityManager::getMetadata();

        $this->assertStringContainsString('entityID="https://localhost/saml"', $metadata);
    }
}
