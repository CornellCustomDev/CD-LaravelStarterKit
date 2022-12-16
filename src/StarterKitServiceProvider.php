<?php

namespace CornellCustomDev\LaravelStarterKit;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class StarterKitServiceProvider extends PackageServiceProvider
{
    public const INSTALL_FILES = [
        'README.md',
        '.lando.yml',
    ];

    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            foreach (self::INSTALL_FILES as $installFileName) {
                $this->publishes([
                    __DIR__."/../project/{$installFileName}" => base_path($installFileName),
                ], "{$this->package->shortName()}-install");
            }
        }
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('starterkit')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command->startWith(fn (InstallCommand $c) => $this->install($c));
            });
    }

    private function install(InstallCommand $command)
    {
        $command->info('Installing StarterKit...');

        $file_list = Arr::join(self::INSTALL_FILES, ', ');
        $shouldInstallFiles = $command->confirm(
            question: "Use Starter Kit files ($file_list)?",
            default: true,
        );

        if ($shouldInstallFiles) {
            $projectName = $command->ask('Project name', Str::title(File::basename(base_path())));
            $this->publishFiles($command);
            $this->populatePlaceholders($projectName);
        }

        $command->info('File installation complete.');
    }

    private function publishFiles(InstallCommand $command)
    {
        $command->call(
            command: 'vendor:publish',
            arguments: [
                '--provider' => StarterKitServiceProvider::class,
                '--tag' => "{$this->package->shortName()}-install",
                '--force' => true,
            ]
        );
    }

    public function populatePlaceholders(string $projectName): void
    {
        $replacements = [
            ':project_name' => $projectName,
            ':project_slug' => Str::slug($projectName),
        ];

        foreach (self::INSTALL_FILES as $file) {
            $contents = File::get(base_path($file));

            $newContents = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $contents
            );

            File::put(base_path($file), $newContents);
        }
    }
}
