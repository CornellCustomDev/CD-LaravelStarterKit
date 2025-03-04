<?php

namespace CornellCustomDev\LaravelStarterKit\CUAuth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateKeys extends Command
{
    protected $signature = 'cu-auth:generate-keys {--force : Overwrite keys if they already exist}';

    protected $description = 'Generate public and private keys for SAML authentication, retrieve CIT public key.';

    public function handle(): void
    {
        $force = $this->option('force');

        $certPath = config('cu-auth.cert-path');
        File::ensureDirectoryExists($certPath);

        $idpCertPath = $certPath.'/idp_cert.pem';
        if ($force || ! File::exists($idpCertPath)) {
            $this->info('Downloading Cornell IDP certificate...');
            $certDownloadUrl = app()->isProduction()
                ? 'https://shibidp.cit.cornell.edu/cornell-idp.cer'
                : 'https://shibidp-test.cit.cornell.edu/cornell-idp.cer';
            $idpCertContents = app()->runningUnitTests()
                ? 'test-idp-cert-contents'  // Dummy content for testing
                : file_get_contents($certDownloadUrl);
            File::put($idpCertPath, $idpCertContents);
        } else {
            $this->info('Cornell IDP certificate already exists.');
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
}
