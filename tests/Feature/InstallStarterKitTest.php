<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature;

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
        $basePath = $this->applicationBasePath();
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
        $this->assertStringContainsString(
            needle: 'test-weill-idp-cert-contents',
            haystack: File::get("$basePath/storage/app/keys/idp_cert.pem")
        );
    }

    public function testDeletesInstallFilesBeforeTests()
    {
        // Confirm no files are in the resources/views directory other than the default welcome.blade.php file
        $basePath = $this->applicationBasePath();
        $files = File::files("$basePath/resources/views");
        $this->assertCount(1, $files);
        $this->assertEquals('welcome.blade.php', $files[0]->getFilename());
        // Confirm any directories in resources/views directory other than "errors" are empty
        $directories = File::directories("$basePath/resources/views");
        foreach ($directories as $directory) {
            if (Str::endsWith($directory, 'errors')) {
                continue;
            }
            $this->assertEmpty(File::files($directory), "$directory should be empty.");
        }

        $this->assertFileDoesNotExist("$basePath/config/cu-auth.php");
        $this->assertFileDoesNotExist("$basePath/config/php-saml-toolkit.php");
        $this->assertDirectoryDoesNotExist("$basePath/storage/app/keys");
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
        $basePath = $this->applicationBasePath();
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
        $basePath = $this->applicationBasePath();
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
                'files', 'assets', 'components', 'examples', 'cu-auth', 'php-saml-toolkit', 'certs', 'certs-weill',
            ])
            ->expectsQuestion('Project name', $projectName)
            ->expectsQuestion('Project description', $projectDescription)
            ->expectsConfirmation('Proceed with installation?', 'yes')
            ->expectsOutputToContain('Installation complete.');
    }

    private function assertContentUpdated(string $projectName, string $projectDescription): void
    {
        $basePath = $this->applicationBasePath();

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
}
