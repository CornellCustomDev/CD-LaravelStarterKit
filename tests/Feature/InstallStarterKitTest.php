<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature;

use CornellCustomDev\LaravelStarterKit\CUAuth\CUAuthServiceProvider;
use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use CornellCustomDev\LaravelStarterKit\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Testing\PendingCommand;

class InstallStarterKitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetInstallFiles();
    }

    public function testCanRunInstall()
    {
        $packageName = StarterKitServiceProvider::PACKAGE_NAME;
        $this->artisan("{$packageName}:install")
            ->expectsQuestion('What would you like to install or update?', ['assets'])
            ->expectsConfirmation('Proceed with installation?')
            ->expectsOutputToContain('Installation aborted.')
            ->assertSuccessful();
    }

    public function testCanRunAllInstallations()
    {
        $basePath = $this->getApplicationBasePath();
        $themeName = StarterKitServiceProvider::THEME_NAME;
        $projectName = 'Test Project';

        $this->installAll($projectName, 'Test description')->assertSuccessful();

        foreach (StarterKitServiceProvider::INSTALL_FILES as $filename) {
            $this->assertFileExists("$basePath/$filename");
        }
        $this->assertFileExists("$basePath/public/$themeName/css/base.css");
        $this->assertFileDoesNotExist("$basePath/public/$themeName/sass/base.scss");
        $this->assertFileExists("$basePath/public/$themeName/favicon.ico");
        $this->assertFileExists("$basePath/resources/views/components/cd/layout/app.blade.php");
        // Comment out until we have form components properly built
        // $this->assertFileExists("$basePath/resources/views/components/cd/form/form-item.blade.php");
        $this->assertFileExists("$basePath/resources/views/examples/cd-index.blade.php");
        $this->assertStringContainsString(
            needle: $projectName,
            haystack: File::get("$basePath/resources/views/examples/cd-index.blade.php")
        );
        $this->assertFileExists("$basePath/config/cu-auth.php");
        $this->assertFileExists("$basePath/config/php-saml-toolkit.php");
        $this->assertFileExists("$basePath/storage/app/keys/idp_cert.pem");
    }

    public function testDeletesInstallFilesBeforeTests()
    {
        // Confirm no files are in the resources/views directory other than the default welcome.blade.php file
        $basePath = $this->getApplicationBasePath();
        $files = File::files("$basePath/resources/views");
        $this->assertCount(1, $files);
        $this->assertEquals('welcome.blade.php', $files[0]->getFilename());
        // Confirm any directories in resources/views directory other than "errors" are empty
        $directories = File::directories("$basePath/resources/views");
        foreach ($directories as $directory) {
            if (Str::endsWith($directory, 'errors')) {
                continue;
            }
            $this->assertEmpty(File::files($directory));
        }
    }

    public function testInstallReplacesFiles()
    {
        $firstProjectName = 'First Project';
        $firstProjectDescription = 'My first new project';
        $this->installAll($firstProjectName, $firstProjectDescription)->assertSuccessful();
        $this->assertContentUpdated($firstProjectName, $firstProjectDescription);

        // Change the content of a css file to demonstrate it gets replaced
        $cssFile = public_path(StarterKitServiceProvider::THEME_NAME.'/css/base.css');
        File::put($cssFile, '/* Updated content */');
        $this->assertStringContainsString('Updated content', File::get($cssFile));
        // Change the cd-index.blade.php file to confirm it gets replaced
        $cdIndexFile = resource_path('views/examples/cd-index.blade.php');
        File::put($cdIndexFile, 'Updated content');
        $this->assertStringContainsString('Updated content', File::get($cdIndexFile));

        $secondProjectName = 'Second Project';
        $secondProjectDescription = 'My second new project';
        $this->installAll($secondProjectName, $secondProjectDescription)->assertSuccessful();
        $this->assertContentUpdated($secondProjectName, $secondProjectDescription);
        // Assets are replaced
        $this->assertStringNotContainsString('Updated content', File::get($cssFile));
        // Example files are replaced
        $this->assertStringNotContainsString('Updated content', File::get($cdIndexFile));
    }

    /**
     * Test that installing just components and examples doesn't update basic project files.
     */
    public function testCanInstallOnlyComponents()
    {
        // Publish install files
        $this->artisan(
            command: 'vendor:publish',
            parameters: [
                '--tag' => StarterKitServiceProvider::PACKAGE_NAME.':files',
                '--force' => true,
            ]
        );
        // Confirm that the readme file has the default content
        $basePath = $this->getApplicationBasePath();
        $readmeContents = File::get("$basePath/README.md");
        $this->assertStringContainsString(':project_name', $readmeContents);

        $this->artisan(StarterKitServiceProvider::PACKAGE_NAME.':install')
            ->expectsQuestion('What would you like to install or update?', ['components', 'examples'])
            ->expectsQuestion('Project name', 'Test Project')
            ->expectsQuestion('Project description', 'Test description')
            ->expectsConfirmation('Proceed with installation?', 'yes')
            ->expectsOutputToContain('Installation complete.')
            ->assertSuccessful();

        // Confirm that the README.md file was not modified
        $readmeContents = File::get("$basePath/README.md");
        $this->assertStringContainsString(':project_name', $readmeContents);
    }

    private function resetInstallFiles(): void
    {
        $basePath = $this->getApplicationBasePath();
        $themeName = StarterKitServiceProvider::THEME_NAME;

        // Delete files from previous tests
        foreach (StarterKitServiceProvider::INSTALL_FILES as $filename) {
            File::delete("$basePath/$filename");
        }
        File::deleteDirectory("$basePath/public/$themeName");
        File::deleteDirectory("$basePath/resources/views/components");
        File::deleteDirectory("$basePath/resources/views/examples");
        File::delete("$basePath/config/cu-auth.php");
        File::delete("$basePath/config/php-saml-toolkit.php");
        File::deleteDirectory("$basePath/storage/app/keys");
    }

    private function installAll(string $projectName, string $projectDescription): PendingCommand
    {
        return $this->artisan(StarterKitServiceProvider::PACKAGE_NAME.':install')
            ->expectsQuestion('What would you like to install or update?', [
                'files', 'assets', 'components', 'examples', 'cu-auth', 'php-saml-toolkit', 'certs',
            ])
            ->expectsQuestion('Project name', $projectName)
            ->expectsQuestion('Project description', $projectDescription)
            ->expectsConfirmation('Proceed with installation?', 'yes')
            ->expectsOutputToContain('Installation complete.');
    }

    private function assertContentUpdated(string $projectName, string $projectDescription): void
    {
        $basePath = $this->getApplicationBasePath();

        $readmeContents = File::get("$basePath/README.md");
        $this->assertStringContainsString($projectName, $readmeContents);
        $this->assertStringContainsString($projectDescription, $readmeContents);

        $envContents = File::get("$basePath/.env.example");
        $this->assertStringContainsString($projectName, $envContents);
        $this->assertStringContainsString(Str::slug($projectName), $envContents);

        $composerConfig = json_decode(File::get("$basePath/composer.json"), true);
        $composerName = StarterKitServiceProvider::COMPOSER_NAMESPACE.'/'.Str::slug($projectName);
        $this->assertEquals($composerName, $composerConfig['name']);
        $this->assertEquals($projectDescription, $composerConfig['description']);

        $landoContents = File::get("$basePath/.lando.yml");
        $this->assertStringContainsString(Str::slug($projectName), $landoContents);
    }

    public function testCanInstallCUAuthConfigFiles()
    {
        $basePath = $this->getApplicationBasePath();
        $defaultVariable = 'REMOTE_USER';
        $testVariable = 'REDIRECT_REMOTE_USER';
        // Make sure we have config values
        $this->refreshApplication();

        $userVariable = config('cu-auth.apache_shib_user_variable');
        $this->assertEquals($defaultVariable, $userVariable);

        $this->artisan(
            command: 'vendor:publish',
            parameters: [
                '--tag' => StarterKitServiceProvider::PACKAGE_NAME.':'.CUAuthServiceProvider::INSTALL_CONFIG_TAG,
                '--force' => true,
            ])
            ->assertSuccessful();

        // Update the config file with a test value for cu-auth.apache_shib_user_variable.
        File::put("$basePath/config/cu-auth.php", str_replace(
            "'$defaultVariable'",
            "'$testVariable'",
            File::get("$basePath/config/cu-auth.php")
        ));
        $this->refreshApplication();

        $userVariable = config('cu-auth.apache_shib_user_variable');
        $this->assertEquals($testVariable, $userVariable);
    }

    public function testCanInstallPhpSamlConfigFiles()
    {
        $basePath = $this->getApplicationBasePath();
        $defaultVariable = 'https://localhost';
        $testVariable = 'https://test.example.com';
        // Make sure we have config values
        $this->refreshApplication();

        $entityId = config('php-saml-toolkit.sp.entityId');
        $this->assertEquals($defaultVariable.'/saml', $entityId);

        $this->artisan(
            command: 'vendor:publish',
            parameters: [
                '--tag' => StarterKitServiceProvider::PACKAGE_NAME.':'.CUAuthServiceProvider::INSTALL_PHP_SAML_TAG,
                '--force' => true,
            ])
            ->assertSuccessful();

        // Update the config file with a test value for php-saml-tookkit.sp.entityId.
        File::put("$basePath/config/php-saml-toolkit.php", str_replace(
            "'$defaultVariable'",
            "'$testVariable'",
            File::get("$basePath/config/php-saml-toolkit.php")
        ));
        $this->refreshApplication();

        $entityId = config('php-saml-toolkit.sp.entityId');
        $this->assertEquals($testVariable.'/saml', $entityId);
    }

    public function testCanInstallSamlCerts()
    {
        $basePath = $this->getApplicationBasePath();
        $idpCertPath = "$basePath/storage/app/keys/idp_cert.pem";
        $spKeyPath = "$basePath/storage/app/keys/sp_key.pem";
        $spCertPath = "$basePath/storage/app/keys/sp_cert.pem";
        $this->assertFileDoesNotExist($idpCertPath);
        $this->assertFileDoesNotExist($spKeyPath);
        $this->assertFileDoesNotExist($spCertPath);

        $this->artisan(StarterKitServiceProvider::PACKAGE_NAME.':install')
            ->expectsQuestion('What would you like to install or update?', ['certs'])
            ->expectsConfirmation('Proceed with installation?', 'yes')
            ->expectsOutputToContain('Installation complete.')
            ->assertSuccessful();

        $this->assertFileExists($idpCertPath);
        $this->assertStringContainsString('test-idp-cert-contents', File::get($idpCertPath));

        $this->assertFileExists($spKeyPath);
        $keyFile = File::get($spKeyPath);
        $this->assertStringContainsString('-----BEGIN PRIVATE KEY-----', $keyFile);
        $this->assertStringContainsString('-----END PRIVATE KEY-----', $keyFile);

        $this->assertFileExists($spCertPath);
        $certFile = File::get($spCertPath);
        $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', $certFile);
        $this->assertStringContainsString('-----END CERTIFICATE-----', $certFile);

        // Confirm we can get the certs via the config
        config(['php-saml-toolkit.idp.x509cert' => File::get("$basePath/storage/app/keys/idp_cert.pem")]);
        $this->assertStringContainsString('test-idp-cert-contents', config('php-saml-toolkit.idp.x509cert'));
    }
}
