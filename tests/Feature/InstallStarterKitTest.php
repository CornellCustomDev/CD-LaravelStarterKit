<?php

namespace CornellCustomDev\LaravelStarterKit\Tests\Feature;

use CornellCustomDev\LaravelStarterKit\StarterKitServiceProvider;
use CornellCustomDev\LaravelStarterKit\Tests\TestCase;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstallStarterKitTest extends TestCase
{
    public function testCanRunInstall()
    {
        $basePath = $this->getBasePath();
        $packageName = StarterKitServiceProvider::PACKAGE_NAME;
        $themeName = StarterKitServiceProvider::THEME_NAME;
        $projectName = 'Test Project';
        $projectDescription = StarterKitServiceProvider::PROJECT_DESCRIPTION;

        // Delete files from previous tests
        foreach (StarterKitServiceProvider::INSTALL_FILES as $filename) {
            File::delete("$basePath/$filename");
        }
        File::deleteDirectory("$basePath/public/$themeName");
        File::deleteDirectory("$basePath/resources/views/components/$themeName");
        File::delete("$basePath/config/cu-auth.php");

        $file_list = Arr::join(StarterKitServiceProvider::INSTALL_FILES, ', ');
        $this->artisan("$packageName:install")
            ->expectsQuestion('Project name', $projectName)
            ->expectsQuestion('Project description', $projectDescription)
            ->expectsConfirmation("Use Starter Kit files ($file_list)?", 'yes')
            ->expectsConfirmation('Install cwd-framework assets?', 'yes')
            ->expectsConfirmation('Install CUAuth config?', 'yes')
            ->expectsOutputToContain('File installation complete.')
            ->assertExitCode(Command::SUCCESS);

        foreach (StarterKitServiceProvider::INSTALL_FILES as $filename) {
            $this->assertFileExists("$basePath/$filename");
        }
        $this->assertFileExists("$basePath/public/$themeName/css/base.css");
        $this->assertFileDoesNotExist("$basePath/public/$themeName/sass/base.scss");
        $this->assertFileExists("$basePath/public/$themeName/favicon.ico");
        $this->assertFileExists("$basePath/resources/views/components/$themeName/layout/app.blade.php");
        $this->assertFileExists("$basePath/resources/views/$themeName-index.blade.php");
        $this->assertStringContainsString(
            needle: $projectName,
            haystack: File::get("$basePath/resources/views/$themeName-index.blade.php")
        );
        $this->assertFileExists("$basePath/config/cu-auth.php");
    }

    public function testInstallReplacesFiles()
    {
        $composerNamespace = StarterKitServiceProvider::COMPOSER_NAMESPACE;
        $firstProjectName = 'First Project';
        $firstProjectDescription = 'My first new project';
        $secondProjectName = 'Second Project';
        $secondProjectDescription = 'My second new project';
        $basePath = $this->getBasePath();
        $packageName = StarterKitServiceProvider::PACKAGE_NAME;

        foreach (StarterKitServiceProvider::INSTALL_FILES as $filename) {
            File::delete("$basePath/$filename");
        }
        $file_list = Arr::join(StarterKitServiceProvider::INSTALL_FILES, ', ');

        $composerConfig = json_decode(File::get("$basePath/composer.json"), true);
        $this->assertArrayHasKey('name', $composerConfig);

        $this->artisan("$packageName:install")
            ->expectsQuestion('Project name', $firstProjectName)
            ->expectsQuestion('Project description', $firstProjectDescription)
            ->expectsConfirmation("Use Starter Kit files ($file_list)?", 'yes')
            ->expectsConfirmation('Install cwd-framework assets?', 'yes')
            ->expectsConfirmation('Install CUAuth config?', 'yes');
        $readmeContents = File::get("$basePath/README.md");
        $envContents = File::get("$basePath/.env.example");
        $composerConfig = json_decode(File::get("$basePath/composer.json"), true);

        $this->assertStringContainsString($firstProjectName, $readmeContents);
        $this->assertStringContainsString($firstProjectDescription, $readmeContents);
        $this->assertStringContainsString($firstProjectName, $envContents);
        $this->assertStringContainsString(Str::slug($firstProjectName), $envContents);
        $this->assertEquals("$composerNamespace/".Str::slug($firstProjectName), $composerConfig['name']);
        $this->assertEquals($firstProjectDescription, $composerConfig['description']);

        $this->artisan("$packageName:install")
            ->expectsQuestion('Project name', $secondProjectName)
            ->expectsQuestion('Project description', $secondProjectDescription)
            ->expectsConfirmation("Use Starter Kit files ($file_list)?", 'yes')
            ->expectsConfirmation('Install cwd-framework assets?', 'yes')
            ->expectsConfirmation('Install CUAuth config?', 'yes');
        $readmeContents = File::get("$basePath/README.md");
        $landoContents = File::get("$basePath/.lando.yml");

        $this->assertStringContainsString($secondProjectName, $readmeContents);
        $this->assertStringContainsString(Str::slug($secondProjectName), $landoContents);
    }

    public function testCanInstallCUAuthConfigFiles()
    {
        $basePath = $this->getBasePath();
        $cuAuthConfigFile = 'config/cu-auth.php';
        $defaultVariable = 'REMOTE_USER';
        $testVariable = 'REDIRECT_REMOTE_USER';

        File::delete("$basePath/$cuAuthConfigFile");
        $this->refreshApplication();

        $userVariable = config('cu-auth.remote_user_variable');
        $this->assertEquals($defaultVariable, $userVariable);

        $this->artisan('vendor:publish --tag=cu-auth-config')
            ->assertExitCode(Command::SUCCESS);

        // Update the config file with a test value for cu-auth.remote_user_variable.
        File::put("$basePath/$cuAuthConfigFile", str_replace(
            "'$defaultVariable'",
            "'$testVariable'",
            File::get("$basePath/$cuAuthConfigFile")
        ));
        $this->refreshApplication();

        $cuAuthUser = config('cu-auth.remote_user_variable');
        $this->assertEquals($testVariable, $cuAuthUser);
    }
}
