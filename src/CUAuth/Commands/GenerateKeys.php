<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OneLogin\Saml2\IdPMetadataParser;

class GenerateKeys extends Command
{
    protected $signature = 'cu-auth:generate-keys {--force : Overwrite keys if they already exist} {--weill : Use Weill Cornell IdP instead of CIT IdP}';

    protected $description = 'Generate public and private keys for SAML authentication, retrieve public key.';

    public function handle(): void
    {
        $force = $this->option('force');
        $weill = $this->option('weill');

        $certPath = config('cu-auth.cert-path');
        File::ensureDirectoryExists($certPath);

        $idpCertPath = $certPath.'/idp_cert.pem';
        if ($force || ! File::exists($idpCertPath)) {
            $this->info('Downloading IDP certificate...');
            $idpCertContents = $this->getIdpCert($weill);
            File::put($idpCertPath, $idpCertContents);
        } else {
            $this->info('IDP certificate already exists.');
        }

        $spKeyPath = $certPath.'/sp_key.pem';
        $spCertPath = $certPath.'/sp_cert.pem';
        if ($force || ! File::exists($spKeyPath) || ! File::exists($spCertPath)) {
            $this->info('Generating service provider key pair...');
            $config = [
                'digest_alg' => 'sha256',
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ];

            // Generate a new private key
            $key = openssl_pkey_new($config);
            openssl_pkey_export_to_file($key, $spKeyPath);

            // Generate a certificate signing request (CSR)
            $csr = openssl_csr_new([], $key, $config);

            // Self-sign the CSR to create a certificate valid for 10 years
            $days = now()->diffInDays(now()->addYears(10));
            $certificate = openssl_csr_sign($csr, null, $key, $days, $config);
            openssl_x509_export_to_file($certificate, $spCertPath);
        } else {
            $this->info('Service provider key pair already exists.');
        }

        $this->info('Keys generated successfully.');
    }

    private function getIdpCert(bool $weill): string|false
    {
        if ($weill) {
            $metadataUrl = app()->isProduction()
                ? 'https://login.weill.cornell.edu/idp/saml2/idp/metadata.php'
                : 'https://login-test.weill.cornell.edu/idp/saml2/idp/metadata.php';
            $testContent = 'test-weill-idp-cert-contents';
        } else {
            $metadataUrl = app()->isProduction()
                ? 'https://shibidp.cit.cornell.edu/idp/shibboleth'
                : 'https://shibidp-test.cit.cornell.edu/idp/shibboleth';
            $testContent = 'test-idp-cert-contents';
        }

        return app()->runningUnitTests()
            ? $testContent // Placeholder content for testing
            : IdPMetadataParser::parseRemoteXML($metadataUrl)['idp']['x509cert'];
    }
}
