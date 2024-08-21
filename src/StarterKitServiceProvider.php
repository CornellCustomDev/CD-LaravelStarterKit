<?php

namespace CornellCustomDev\LaravelStarterKit;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

class StarterKitServiceProvider extends PackageServiceProvider
{
    const PACKAGE_NAME = 'starterkit';

    const THEME_NAME = 'cwd-framework';

    const COMPOSER_NAMESPACE = 'cornell-custom-dev';

    const PROJECT_DESCRIPTION = 'A project built from Cornell Custom Dev Laravel Starter Kit.';

    public const INSTALL_FILES = [
        'README.md',
        '.env.example',
        '.gitignore',
        '.lando.yml',
        // Comment this out until we need to include mod_shib config
        // 'public/.htaccess',
    ];

    public const ASSET_FILES = [
        'css',
        'fonts',
        'images',
        'js',
        'favicon.ico',
    ];

    public const EXAMPLE_FILES = [
        'resources/views/examples/cd-index.blade.php',
        'resources/views/examples/form-example.blade.php',
        'resources/views/examples/livewire-form-example.blade.php',
    ];

    public function boot()
    {
        parent::boot();

        $themeDir = '/vendor/cubear/cwd_framework_lite';
        $themeAssetsPath = File::isDirectory(base_path().$themeDir) ? base_path() : __DIR__.'/..';
        $filesSourcePath = __DIR__.'/..';

        if ($this->app->runningInConsole()) {
            foreach (self::INSTALL_FILES as $installFileName) {
                $this->publishes([
                    __DIR__."/../project/{$installFileName}" => base_path($installFileName),
                ], self::PACKAGE_NAME.':files');
            }

            foreach (self::ASSET_FILES as $asset_file) {
                $this->publishes([
                    $themeAssetsPath.$themeDir.'/'.$asset_file => public_path(self::THEME_NAME.'/'.$asset_file),
                ], self::THEME_NAME.':assets');
            }

            $this->publishes([
                "$filesSourcePath/resources/views/components/cd/dialog" => resource_path('/views/components/cd/dialog'),
                "$filesSourcePath/resources/views/components/cd/form" => resource_path('/views/components/cd/form'),
                "$filesSourcePath/resources/views/components/cd/layout" => resource_path('/views/components/cd/layout'),
            ], self::PACKAGE_NAME.':components');

            foreach (self::EXAMPLE_FILES as $exampleFile) {
                $this->publishes([
                    "$filesSourcePath/$exampleFile" => base_path($exampleFile),
                ], self::PACKAGE_NAME.':examples');
            }
        }
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name(self::PACKAGE_NAME)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->startWith(fn (InstallCommand $c) => $this->install($c));
            });
    }

    private function install(InstallCommand $command)
    {
        info('Installing StarterKit...');

        $install = collect(multiselect(
            label: 'What would you like to install or update?',
            options: [
                'files' => 'Basic project files (README, .env.example, etc.) and update composer.json',
                'assets' => 'Theme assets from CWD Framework Lite',
                'components' => 'View components (/resources/views/components/cd)',
                'examples' => 'Example blade files',
            ],
            default: ['files', 'assets', 'components'],
            required: true,
            hint: 'Note: Any existing files will be replaced for the selected options.',
        ));

        $installFiles = $install->contains('files');
        $installExamples = $install->contains('examples');

        if ($installFiles || $installExamples) {
            $basePathTitle = Str::title(File::basename(base_path()));
            $composerConfig = json_decode(File::get(base_path('composer.json')), true);
            // Turn something like "cornell-custom-dev/laravel-demo" into "Laravel Demo"
            $composerTitle = Str::title(str_replace('-', ' ', Str::after($composerConfig['name'], '/')));
            $projectName = suggest(
                label: 'Project name',
                options: array_filter([$basePathTitle, $composerTitle]),
                required: true,
                hint: 'This is used in the README and examples, and slugified for use in .env, composer.json, etc.',
            );

            if ($installFiles) {
                $projectDescription = text(
                    label: 'Project description',
                    default: self::PROJECT_DESCRIPTION,
                    required: true,
                    hint: 'This is used in the README and composer.json.',
                );
            }
        }

        // Confirm before proceeding
        if (! confirm('Proceed with installation?')) {
            info('Installation aborted.');

            return;
        }

        if ($installFiles) {
            $this->publishTag($command, self::PACKAGE_NAME.':files');
            $this->populatePlaceholders(self::INSTALL_FILES, $projectName, $projectDescription);
            $this->updateComposerJson($projectName, $projectDescription);
        }

        if ($install->contains('assets')) {
            $this->publishTag($command, self::THEME_NAME.':assets');
        }

        if ($install->contains('components')) {
            $this->publishTag($command, self::PACKAGE_NAME.':components');
        }

        if ($installExamples) {
            $this->publishTag($command, self::PACKAGE_NAME.':examples');
            $this->populatePlaceholders(self::EXAMPLE_FILES, $projectName);
        }

        info('Installation complete.');
    }

    private function publishTag(InstallCommand $command, string $tag): void
    {
        $command->call(
            command: 'vendor:publish',
            arguments: [
                '--provider' => StarterKitServiceProvider::class,
                '--tag' => $tag,
                '--force' => true,
            ]
        );
    }

    public static function populatePlaceholders($files, string $projectName, ?string $projectDescription = null): void
    {
        $replacements = [
            ':project_name' => $projectName,
            ':project_slug' => Str::slug($projectName),
            ':project_description' => $projectDescription ?? self::PROJECT_DESCRIPTION,
        ];

        foreach ($files as $file) {
            $contents = File::get(base_path($file));

            $newContents = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $contents
            );

            File::put(base_path($file), $newContents);
        }
    }

    private function updateComposerJson(string $projectName, string $projectDescription): void
    {
        $composerFile = base_path('composer.json');
        $composerConfig = json_decode(File::get($composerFile), true);

        $replacements = [
            'name' => self::COMPOSER_NAMESPACE.'/'.Str::slug($projectName),
            'description' => $projectDescription,
        ];

        foreach ($replacements as $key => $replacement) {
            $composerConfig[$key] = $replacement;
        }

        File::put($composerFile, json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
